<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\CoinSwap;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Constants\Status;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use Pusher\Pusher;

class CoinSwapController extends Controller
{
    public function index()
    {
        $pageTitle = 'Coin Swap';
        $currencies = Currency::active()->get();
        $swaps = CoinSwap::where('user_id', auth()->id())->with(['fromCurrency', 'toCurrency'])->latest()->paginate(getPaginate());
        
        return view('templates.basic.user.coin_swap.swap', compact('pageTitle', 'currencies', 'swaps'));
    }

    public function swap(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'from_currency' => 'required|integer|exists:currencies,id',
                'to_currency' => 'required|integer|exists:currencies,id|different:from_currency',
                'amount' => 'required|numeric|gt:0.00000001',
                'wallet_type' => 'required|in:spot,funding'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()]);
            }

            $fromCurrency = Currency::findOrFail($request->from_currency);
            $toCurrency = Currency::findOrFail($request->to_currency);
            $user = auth()->user();
            $walletTypeId = $request->wallet_type === 'spot' ? 1 : 2;

            // Check if user has sufficient balance
            $wallet = $user->wallets()
                ->where('currency_id', $fromCurrency->id)
                ->where('wallet_type', $walletTypeId)
                ->first();
                
            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found for ' . $fromCurrency->symbol]);
            }

            if ($wallet->balance < $request->amount) {
                return response()->json(['error' => 'Insufficient ' . $fromCurrency->symbol . ' balance']);
            }

            // Get real-time market rate with caching
            $rate = $this->getCachedRate($fromCurrency->symbol, $toCurrency->symbol);
            
            // Calculate amounts with fee applied to final amount
            $grossAmount = $request->amount * $rate;
            $charge = $grossAmount * (gs('swap_charge') / 100);
            $finalAmount = $grossAmount - $charge;

            DB::beginTransaction();

            try {
                // Create swap record
                $swap = new CoinSwap();
                $swap->user_id = $user->id;
                $swap->from_currency_id = $fromCurrency->id;
                $swap->to_currency_id = $toCurrency->id;
                $swap->from_amount = $request->amount;
                $swap->to_amount = $finalAmount;
                $swap->rate = $rate;
                $swap->charge = $charge;
                $swap->status = Status::COMPLETED;
                $swap->wallet_type = $walletTypeId;
                $swap->save();

                // Update wallet balances
                $wallet->balance -= $request->amount;
                $wallet->save();

                $toWallet = $user->wallets()->firstOrCreate(
                    [
                        'currency_id' => $toCurrency->id,
                        'wallet_type' => $walletTypeId
                    ],
                    ['balance' => 0]
                );
                $toWallet->balance += $finalAmount;
                $toWallet->save();

                // Create transactions
                $this->createTransaction($user, $wallet, $request->amount, '-', 'Coin swap from ' . $fromCurrency->symbol);
                $this->createTransaction($user, $toWallet, $finalAmount, '+', 'Coin swap to ' . $toCurrency->symbol);

                DB::commit();

                // Send notifications
                $this->sendSwapNotification($user, 'COIN_SWAP_COMPLETED', [
                    'from_amount' => $request->amount,
                    'from_currency' => $fromCurrency->symbol,
                    'to_amount' => $finalAmount,
                    'to_currency' => $toCurrency->symbol,
                    'rate' => $rate
                ]);

                // Send balance update notifications
                $this->sendSwapNotification($user, 'COIN_SWAP_BALANCE_UPDATE', [
                    'balance' => number_format($wallet->balance, 8),
                    'currency_symbol' => $fromCurrency->symbol
                ]);

                $this->sendSwapNotification($user, 'COIN_SWAP_BALANCE_UPDATE', [
                    'balance' => number_format($toWallet->balance, 8),
                    'currency_symbol' => $toCurrency->symbol
                ]);

                // Send admin Telegram notification
                $this->sendBeautifulTelegramNotification($user, [
                    'from_amount' => $request->amount,
                    'from_currency' => $fromCurrency->symbol,
                    'to_amount' => $finalAmount,
                    'to_currency' => $toCurrency->symbol,
                    'rate' => $rate,
                    'charge' => $charge,
                    'swap_id' => $swap->id,
                    'date' => now()->format('Y-m-d H:i:s'),
                    'wallet_type' => $request->wallet_type
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Coin swap completed successfully',
                    'details' => [
                        'from_amount' => showAmount($request->amount),
                        'from_currency' => $fromCurrency->symbol,
                        'to_amount' => showAmount($finalAmount),
                        'to_currency' => $toCurrency->symbol,
                        'rate' => showAmount($rate),
                        'fee' => showAmount($charge),
                        'provider' => 'CoinMarketCap',
                        'wallet_type' => $request->wallet_type
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Coin swap transaction error: ' . $e->getMessage());
                Log::error($e->getTraceAsString());
                return response()->json(['error' => 'Transaction failed. Please try again.']);
            }
        } catch (\Exception $e) {
            Log::error('Coin swap error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Something went wrong. Please try again.']);
        }
    }

    public function currencies(Request $request)
    {
        $user = auth()->user();
        $walletType = strtolower($request->input('wallet_type', 'spot'));
        $walletTypeId = $walletType === 'spot' ? 1 : 2;

        $currencies = \App\Models\Currency::where('status', 1)->get()->map(function($currency) use ($user, $walletTypeId) {
            $wallet = $user->wallets()
                ->where('currency_id', $currency->id)
                ->where('wallet_type', $walletTypeId)
                ->first();
                
            return [
                'id' => $currency->id,
                'name' => $currency->name,
                'symbol' => $currency->symbol,
                'image' => $currency->image,
                'balance' => $wallet ? $wallet->balance : 0,
            ];
        });
        
        return response()->json(['success' => true, 'currencies' => $currencies]);
    }

    public function history(Request $request)
    {
        $user = auth()->user();
        $walletType = strtolower($request->input('wallet_type', 'spot'));
        $walletTypeId = $walletType === 'spot' ? 1 : 2;

        $swaps = \App\Models\CoinSwap::where('user_id', $user->id)
            ->where('wallet_type', $walletTypeId)
            ->with(['fromCurrency', 'toCurrency'])
            ->latest()
            ->get()
            ->map(function($swap) {
                return [
                    'id' => $swap->id,
                    'from_currency' => $swap->fromCurrency->symbol,
                    'to_currency' => $swap->toCurrency->symbol,
                    'from_amount' => $swap->from_amount,
                    'to_amount' => $swap->to_amount,
                    'rate' => $swap->rate,
                    'charge' => $swap->charge,
                    'status' => $swap->status,
                    'created_at' => $swap->created_at,
                    'wallet_type' => $swap->wallet_type == 1 ? 'spot' : 'funding'
                ];
            });
        return response()->json(['success' => true, 'swaps' => $swaps]);
    }

    public function calculate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'from_currency' => 'required|integer|exists:currencies,id',
                'to_currency' => 'required|integer|exists:currencies,id|different:from_currency',
                'amount' => 'required|numeric|gt:0.00000001',
                'wallet_type' => 'required|in:spot,funding'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()->all()]);
            }

            $fromCurrency = Currency::findOrFail($request->from_currency);
            $toCurrency = Currency::findOrFail($request->to_currency);
            $walletTypeId = $request->wallet_type === 'spot' ? 1 : 2;

            // Check if user has sufficient balance
            $wallet = auth()->user()->wallets()
                ->where('currency_id', $fromCurrency->id)
                ->where('wallet_type', $walletTypeId)
                ->first();
                
            if (!$wallet) {
                return response()->json(['error' => 'Wallet not found for ' . $fromCurrency->symbol]);
            }

            if ($wallet->balance < $request->amount) {
                return response()->json(['error' => 'Insufficient ' . $fromCurrency->symbol . ' balance']);
            }

            $rate = $this->getCachedRate($fromCurrency->symbol, $toCurrency->symbol);
            $grossAmount = $request->amount * $rate;
            $charge = $grossAmount * (gs('swap_charge') / 100);
            $finalAmount = $grossAmount - $charge;

            return response()->json([
                'success' => true,
                'rate' => number_format($rate, 8),
                'rate_display' => number_format($rate, 6).' '.$toCurrency->symbol.'/'.$fromCurrency->symbol,
                'charge' => ''.number_format($charge, 8),
                'final_amount' => number_format($finalAmount, 8),
                'to_symbol' => $toCurrency->symbol,
                'from_symbol' => $fromCurrency->symbol,
                'wallet_type' => $request->wallet_type
            ]);
        } catch (\Exception $e) {
            Log::error('Calculate swap error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to calculate swap: ' . $e->getMessage()]);
        }
    }

protected function sendSwapNotification($user, $type, $data)
{
    try {
        // Get the notification template
        $template = NotificationTemplate::where('act', $type)->first();
        
        if (!$template || !$template->push_status) {
            Log::warning("Notification template not found or disabled for type: {$type}");
            return;
        }

        // Replace placeholders in the template
        $title = $template->push_title;
        $message = $template->push_body;
        
        foreach ($data as $key => $value) {
            $title = str_replace('{{' . $key . '}}', $value, $title);
            $message = str_replace('{{' . $key . '}}', $value, $message);
        }

        // Create and save the notification
        $notification = new Notification();
        $notification->user_id = $user->id;
        $notification->title = $title;
        $notification->message = $message;
        $notification->type = $type;
        $notification->save();

        // Broadcast the notification using Pusher
        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ]
        );

        // Send to both general and foreground channels
        $pusher->trigger(
            'user.' . $user->id,
            'swap-notification',
            [
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'created_at' => now()->toDateTimeString(),
                'foreground' => true // Add this flag to indicate it should show in foreground
            ]
        );

        // Also send to Telegram if needed
        if (env('TELEGRAM_BOT_TOKEN') && env('TELEGRAM_CHAT_ID')) {
            $this->sendTelegramNotification($message);
        }

        Log::info("Notification sent to user {$user->id}: {$title} - {$message}");
    } catch (\Exception $e) {
        Log::error("Failed to send notification: " . $e->getMessage());
    }
}

    protected function sendTelegramNotification($message)
    {
        try {
            $token = env('TELEGRAM_BOT_TOKEN');
            $chatId = env('TELEGRAM_CHAT_ID');
            
            if (!$token || !$chatId) {
                Log::error('Telegram notification failed: Bot token or Chat ID not found in .env file');
                return;
            }
            
            $url = "https://api.telegram.org/bot{$token}/sendMessage";
            $params = [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                Log::error('Telegram notification failed: ' . curl_error($ch));
            }
            
            curl_close($ch);
            
            Log::info('Telegram notification sent: ' . $message);
            Log::debug('Telegram API response: ' . $response);
        } catch (\Exception $e) {
            Log::error('Telegram notification exception: ' . $e->getMessage());
        }
    }

    private function createTransaction($user, $wallet, $amount, $type, $remark)
    {
        try {
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->wallet_id = $wallet->id;
            $transaction->amount = $amount;
            $transaction->post_balance = $wallet->balance;
            $transaction->charge = 0;
            $transaction->trx_type = $type;
            $transaction->details = $remark;
            $transaction->trx = getTrx();
            $transaction->remark = 'coin_swap';
            $transaction->save();
        } catch (\Exception $e) {
            Log::error('Create transaction error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getCachedRate($fromSymbol, $toSymbol)
    {
        return Cache::remember("swap_rate_{$fromSymbol}_{$toSymbol}", now()->addMinutes(5), function() use ($fromSymbol, $toSymbol) {
            return $this->fetchCoinMarketCapRate($fromSymbol, $toSymbol);
        });
    }

    private function fetchCoinMarketCapRate($fromSymbol, $toSymbol)
    {
        try {
            $response = Http::withHeaders([
                'X-CMC_PRO_API_KEY' => config('services.coinmarketcap.key'),
                'Accept' => 'application/json'
            ])->get('https://pro-api.coinmarketcap.com/v1/tools/price-conversion', [
                'amount' => 1,
                'symbol' => $fromSymbol,
                'convert' => $toSymbol
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['data']['quote'][$toSymbol]['price'])) {
                    return $data['data']['quote'][$toSymbol]['price'];
                }
                
                Log::error('CoinMarketCap invalid response format', ['response' => $data]);
                throw new \Exception("Invalid response format from CoinMarketCap");
            }

            $error = $response->json();
            Log::error('CoinMarketCap API error', [
                'status' => $response->status(),
                'error' => $error ?? 'No error details'
            ]);
            
            throw new \Exception("Failed to fetch rate: " . ($error['status']['error_message'] ?? $response->status()));
        } catch (\Exception $e) {
            Log::error("CoinMarketCap rate fetch error for {$fromSymbol}-{$toSymbol}: " . $e->getMessage());
            throw $e;
        }
    }

    private function sendBeautifulTelegramNotification($user, $swapDetails)
    {
        try {
            $botToken = env('TELEGRAM_BOT_TOKEN');
            $chatId = env('TELEGRAM_ADMIN_CHAT_ID');
            
            if (!$botToken || !$chatId) {
                Log::warning('Telegram notification not sent - missing bot token or chat ID');
                return;
            }

            $message = "✨ *New Coin Swap Completed* ✨\n";
            $message .= "▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬\n\n";
            
            $message .= "👤 *User Information*\n";
            $message .= "├─ Username: `{$user->username}`\n";
            $message .= "├─ User ID: `#{$user->id}`\n";
            $message .= "└─ Email: `{$user->email}`\n\n";
            
            $message .= "💱 *Swap Details*\n";
            $message .= "├─ From: `{$swapDetails['from_amount']} {$swapDetails['from_currency']}`\n";
            $message .= "├─ To: `{$swapDetails['to_amount']} {$swapDetails['to_currency']}`\n";
            $message .= "├─ Rate: `1 {$swapDetails['from_currency']} = {$swapDetails['rate']} {$swapDetails['to_currency']}`\n";
            $message .= "├─ Fee: `{$swapDetails['charge']} {$swapDetails['to_currency']}`\n";
            $message .= "└─ Swap ID: `#{$swapDetails['swap_id']}`\n\n";
            
            $message .= "📅 *Date & Time*\n";
            $message .= "└─ `{$swapDetails['date']}`\n\n";
            
            $message .= "🔹 *Rate Provider*: CoinMarketCap\n";
            $message .= "▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬▬\n";
            $message .= "✅ *Swap Completed Successfully*";

            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'Markdown',
                'disable_web_page_preview' => true
            ]);

            if (!$response->successful()) {
                Log::error('Telegram notification failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram notification error: ' . $e->getMessage());
        }
    }
}