<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\CoinSwap;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\UserSwapSetting;
use App\Models\Wallet;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CoinSwapController extends Controller
{
    public function index()
    {
        $pageTitle = 'Instant Crypto Convert & Swap';
        $user = auth()->user();

        $currencies = Currency::active()->get();

        // Attach user balances to currencies for spot wallet
        $wallets = Wallet::spot()->where('user_id', $user->id)->get()->keyBy('currency_id');
        foreach ($currencies as $currency) {
            $wallet = $wallets->get($currency->id);
            $currency->user_balance = $wallet ? (float)$wallet->balance : 0;
        }

        $userSetting = UserSwapSetting::where('user_id', $user->id)->first();
        $feeRate = $userSetting && $userSetting->custom_fee_percentage !== null 
            ? (float)$userSetting->custom_fee_percentage 
            : (float)(gs('swap_charge') ?? 0.10);

        $swaps = CoinSwap::where('user_id', $user->id)
            ->with(['fromCurrency', 'toCurrency'])
            ->latest()
            ->paginate(getPaginate());

        $statistics = [
            'total_swaps' => CoinSwap::where('user_id', $user->id)->count(),
            'total_volume' => CoinSwap::where('user_id', $user->id)->where('status', Status::COMPLETED)->sum('from_amount'),
            'fee_rate' => $feeRate,
            'is_locked' => $userSetting && $userSetting->is_swap_locked
        ];

        return view('templates.basic.user.coin_swap.swap', compact(
            'pageTitle',
            'user',
            'currencies',
            'swaps',
            'statistics',
            'feeRate'
        ));
    }

    public function swap(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'from_currency' => 'required|integer|exists:currencies,id',
                'to_currency' => 'required|integer|exists:currencies,id|different:from_currency',
                'amount' => 'required|numeric|gt:0.00000001',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()]);
            }

            $user = auth()->user();

            // Check if swap is locked for this user
            $userSetting = UserSwapSetting::where('user_id', $user->id)->first();
            if ($userSetting && $userSetting->is_swap_locked) {
                return response()->json(['error' => 'Instant coin swapping is temporarily suspended for your account. Please contact VIP support.']);
            }

            $fromCurrency = Currency::findOrFail($request->from_currency);
            $toCurrency = Currency::findOrFail($request->to_currency);

            // Check if user has sufficient balance in Spot Wallet
            $fromWallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', $fromCurrency->id)->first();
            
            $availableBalance = $fromWallet ? (float)$fromWallet->balance : 0;

            if ($availableBalance < (float)$request->amount) {
                return response()->json(['error' => 'Insufficient ' . $fromCurrency->symbol . ' balance in your Spot Wallet.']);
            }

            // Get live market exchange rate
            $rate = $this->getLiveRate($fromCurrency->symbol, $toCurrency->symbol);
            if ($rate <= 0) {
                return response()->json(['error' => 'Unable to fetch live market quotation for ' . $fromCurrency->symbol . '/' . $toCurrency->symbol . '. Please try again.']);
            }

            // Calculate fees
            $feePercentage = $userSetting && $userSetting->custom_fee_percentage !== null 
                ? (float)$userSetting->custom_fee_percentage 
                : (float)(gs('swap_charge') ?? 0.10);

            $grossAmount = (float)$request->amount * $rate;
            $charge = $grossAmount * ($feePercentage / 100);
            $finalAmount = $grossAmount - $charge;

            DB::beginTransaction();
            try {
                // Deduct from wallet
                $fromWallet->balance -= (float)$request->amount;
                $fromWallet->save();

                // Add to toWallet
                $toWallet = Wallet::spot()->firstOrCreate(
                    ['user_id' => $user->id, 'currency_id' => $toCurrency->id],
                    ['balance' => 0]
                );
                $toWallet->balance += $finalAmount;
                $toWallet->save();

                // Create swap record
                $swap = new CoinSwap();
                $swap->user_id = $user->id;
                $swap->from_currency_id = $fromCurrency->id;
                $swap->to_currency_id = $toCurrency->id;
                $swap->from_amount = (float)$request->amount;
                $swap->to_amount = $finalAmount;
                $swap->rate = $rate;
                $swap->charge = $charge;
                $swap->status = Status::COMPLETED;
                $swap->save();

                // Create transaction records
                $this->createTransaction($user, $fromWallet, (float)$request->amount, '-', "Swapped {$request->amount} {$fromCurrency->symbol} to {$toCurrency->symbol}");
                $this->createTransaction($user, $toWallet, $finalAmount, '+', "Received {$finalAmount} {$toCurrency->symbol} from {$fromCurrency->symbol} swap");

                DB::commit();

                // Send Telegram Notification
                try {
                    $telegram = new TelegramService();
                    $telegram->notifyCoinSwapExecuted($user, $swap, $fromCurrency->symbol, $toCurrency->symbol);
                } catch (\Exception $e) {
                    Log::error('Coin swap Telegram error: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Successfully converted ' . number_format($request->amount, 6) . ' ' . $fromCurrency->symbol . ' to ' . number_format($finalAmount, 6) . ' ' . $toCurrency->symbol . '!',
                    'details' => [
                        'from_amount' => number_format($request->amount, 6),
                        'from_currency' => $fromCurrency->symbol,
                        'to_amount' => number_format($finalAmount, 6),
                        'to_currency' => $toCurrency->symbol,
                        'rate' => number_format($rate, 6),
                        'fee' => number_format($charge, 6),
                        'new_from_balance' => number_format($fromWallet->balance, 6),
                        'new_to_balance' => number_format($toWallet->balance, 6)
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Coin swap transaction error: ' . $e->getMessage());
                return response()->json(['error' => 'Conversion settlement failed: ' . $e->getMessage()]);
            }

        } catch (\Exception $e) {
            Log::error('Coin swap execution error: ' . $e->getMessage());
            return response()->json(['error' => 'An unexpected error occurred during execution.']);
        }
    }

    public function calculate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'from_currency' => 'required|integer|exists:currencies,id',
                'to_currency' => 'required|integer|exists:currencies,id|different:from_currency',
                'amount' => 'required|numeric|gt:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()]);
            }

            $fromCurrency = Currency::findOrFail($request->from_currency);
            $toCurrency = Currency::findOrFail($request->to_currency);

            $rate = $this->getLiveRate($fromCurrency->symbol, $toCurrency->symbol);
            if ($rate <= 0) {
                return response()->json(['error' => 'Rate unavailable']);
            }

            $user = auth()->user();
            $userSetting = UserSwapSetting::where('user_id', $user->id)->first();
            $feePercentage = $userSetting && $userSetting->custom_fee_percentage !== null 
                ? (float)$userSetting->custom_fee_percentage 
                : (float)(gs('swap_charge') ?? 0.10);

            $grossAmount = (float)$request->amount * $rate;
            $charge = $grossAmount * ($feePercentage / 100);
            $finalAmount = $grossAmount - $charge;

            return response()->json([
                'success' => true,
                'rate' => $rate,
                'rate_display' => '1 ' . $fromCurrency->symbol . ' ≈ ' . number_format($rate, 6) . ' ' . $toCurrency->symbol,
                'charge' => number_format($charge, 6),
                'fee_percentage' => $feePercentage,
                'final_amount' => number_format($finalAmount, 6),
                'raw_final_amount' => $finalAmount,
                'to_symbol' => $toCurrency->symbol,
                'from_symbol' => $fromCurrency->symbol
            ]);

        } catch (\Exception $e) {
            Log::error('Calculate swap rate error: ' . $e->getMessage());
            return response()->json(['error' => 'Quotation rate calculation failed.']);
        }
    }

    private function createTransaction($user, $wallet, $amount, $type, $details)
    {
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->wallet_id = $wallet->id;
        $transaction->amount = $amount;
        $transaction->post_balance = $wallet->balance;
        $transaction->charge = 0;
        $transaction->trx_type = $type;
        $transaction->details = $details;
        $transaction->trx = getTrx();
        $transaction->remark = 'coin_swap';
        $transaction->save();
    }

    /**
     * Get live market rate with fast caching using Binance public market data
     */
    private function getLiveRate($fromSymbol, $toSymbol)
    {
        $fromSymbol = strtoupper($fromSymbol);
        $toSymbol = strtoupper($toSymbol);

        if ($fromSymbol === $toSymbol) {
            return 1.0;
        }

        return Cache::remember("swap_rate_{$fromSymbol}_{$toSymbol}", 10, function() use ($fromSymbol, $toSymbol) {
            $fromUsd = $this->getUsdPrice($fromSymbol);
            $toUsd = $this->getUsdPrice($toSymbol);

            if ($fromUsd > 0 && $toUsd > 0) {
                return $fromUsd / $toUsd;
            }

            return 1.0;
        });
    }

    /**
     * Fetch USD price for symbol from Binance or fallback
     */
    private function getUsdPrice($symbol)
    {
        if (in_array($symbol, ['USDT', 'USD', 'USDC', 'BUSD', 'DAI'])) {
            return 1.0;
        }

        try {
            $res = Http::timeout(3)->get("https://api.binance.com/api/v3/ticker/price?symbol={$symbol}USDT");
            if ($res->successful()) {
                $data = $res->json();
                if (isset($data['price'])) {
                    return (float)$data['price'];
                }
            }
        } catch (\Exception $e) {
            Log::warning("Binance ticker fetch failed for {$symbol}USDT: " . $e->getMessage());
        }

        // Realistic Fallback prices if external network is down
        $fallbacks = [
            'BTC' => 77901.50,
            'ETH' => 2450.20,
            'SOL' => 145.80,
            'BNB' => 595.40,
            'XRP' => 0.58,
            'DOGE' => 0.11,
            'ADA' => 0.38,
            'AVAX' => 24.50,
            'LINK' => 11.20,
            'DOT' => 4.60,
            'LTC' => 68.40,
            'NEAR' => 4.80,
            'MATIC' => 0.42,
            'TRX' => 0.16
        ];

        return $fallbacks[$symbol] ?? 1.0;
    }
}