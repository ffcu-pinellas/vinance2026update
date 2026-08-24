<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\AiBotPlan;
use App\Models\AiTradeLog;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\UserAiBot;
use App\Models\UserAiSetting;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AITraderController extends Controller
{
    public static function getLiveCryptoPrice($pairSymbol = 'BTC/USDT')
    {
        $cleanSymbol = strtoupper(str_replace(['/', '_', '-'], '', $pairSymbol));
        
        // 1. Check CoinPair table in database
        try {
            $coinSym = explode('/', str_replace('_', '/', $pairSymbol))[0];
            $pair = \App\Models\CoinPair::whereHas('coin', function($q) use ($coinSym) {
                $q->where('symbol', $coinSym);
            })->first();
            if ($pair && $pair->price > 0) {
                return (float)$pair->price;
            }
        } catch (\Exception $e) {}

        // 2. Fetch directly from Binance live API
        try {
            $res = \App\Lib\CurlRequest::curlContent("https://api.binance.com/api/v3/ticker/price?symbol={$cleanSymbol}");
            $json = json_decode($res, true);
            if (isset($json['price']) && $json['price'] > 0) {
                return (float)$json['price'];
            }
        } catch (\Exception $e) {}

        // 3. Fallbacks matching current market
        $fallbacks = [
            'BTCUSDT' => 77900.00,
            'ETHUSDT' => 3120.00,
            'SOLUSDT' => 195.00,
            'BNBUSDT' => 640.00,
            'XRPUSDT' => 0.584,
            'AVAXUSDT' => 28.50,
            'SUIUSDT' => 1.95,
            'DOGEUSDT' => 0.142,
            'NEARUSDT' => 4.85,
        ];

        return $fallbacks[$cleanSymbol] ?? 100.00;
    }

    protected function processAutoBotTrading($user, $activeBots)
    {
        foreach ($activeBots as $userBot) {
            $plan = $userBot->plan;
            if (!$plan) continue;

            $lastTrade = AiTradeLog::where('user_ai_bot_id', $userBot->id)->latest()->first();
            $shouldTrade = false;

            if (!$lastTrade) {
                $shouldTrade = true;
            } elseif ($lastTrade->created_at->diffInMinutes(now()) >= 3) {
                // Execute a periodic micro-trade every 3+ minutes
                $shouldTrade = true;
            }

            if ($shouldTrade) {
                $pairs = is_array($plan->trading_pairs) && count($plan->trading_pairs) > 0 
                    ? $plan->trading_pairs 
                    : ['BTC/USDT', 'ETH/USDT', 'SOL/USDT'];
                
                $randomPair = $pairs[array_rand($pairs)];
                $entryPrice = self::getLiveCryptoPrice($randomPair);

                $minTradePct = max(0.20, ($plan->daily_roi_min / 4.0));
                $maxTradePct = max(0.55, ($plan->daily_roi_max / 3.0));
                $randomTradePct = round(mt_rand($minTradePct * 100, $maxTradePct * 100) / 100, 2);

                $exitPrice = $entryPrice * (1 + ($randomTradePct / 100));
                $tradeVolume = $userBot->allocated_amount * round(mt_rand(15, 30) / 100, 2);
                $tradeProfit = round($tradeVolume * ($randomTradePct / 100), 4);

                $trade = new AiTradeLog();
                $trade->user_id = $user->id;
                $trade->user_ai_bot_id = $userBot->id;
                $trade->pair_symbol = $randomPair;
                $trade->side = mt_rand(0, 1) ? 'BUY' : 'SELL';
                $trade->entry_price = $entryPrice;
                $trade->exit_price = $exitPrice;
                $trade->amount = $tradeVolume;
                $trade->profit_amount = $tradeProfit;
                $trade->profit_percentage = $randomTradePct;
                $trade->status = 'closed';
                $trade->save();

                $userBot->current_profit += $tradeProfit;
                $userBot->total_trades += 1;
                $userBot->save();
            }
        }
    }

    public function index()
    {
        $pageTitle = 'Vinance AI Quantitative Terminal';
        $user = auth()->user();

        $usdt = Currency::where('symbol', 'USDT')->first();
        $spotWallet = null;
        $fundingWallet = null;

        if ($usdt) {
            $spotWallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', $usdt->id)->first();
            $fundingWallet = Wallet::funding()->where('user_id', $user->id)->where('currency_id', $usdt->id)->first();
        }

        $spotBalance = $spotWallet ? $spotWallet->balance : $user->balance;
        $fundingBalance = $fundingWallet ? $fundingWallet->balance : 0;

        // Bot plans
        $plans = AiBotPlan::active()->orderBy('rank')->get();

        // User's active & completed bots
        $userBots = UserAiBot::with('plan')->where('user_id', $user->id)->latest()->get();
        $activeBots = $userBots->where('status', 1);

        // Auto accumulate realistic bot profits over time while running
        if ($activeBots->count() > 0) {
            $this->processAutoBotTrading($user, $activeBots);
            // Refresh user bots
            $userBots = UserAiBot::with('plan')->where('user_id', $user->id)->latest()->get();
            $activeBots = $userBots->where('status', 1);
        }

        // User settings & overrides
        $userSetting = UserAiSetting::where('user_id', $user->id)->first();

        // Total stats
        $totalAllocated = $activeBots->sum('allocated_amount');
        $totalProfit = $userBots->sum('current_profit');
        $totalTrades = $userBots->sum('total_trades');
        
        $winRate = $userSetting && $userSetting->custom_win_rate 
            ? $userSetting->custom_win_rate 
            : 96.80;

        // Trade logs for this user
        $tradeLogs = AiTradeLog::where('user_id', $user->id)->latest()->take(30)->get();

        return view('templates.basic.user.ai_trader', compact(
            'pageTitle',
            'user',
            'spotBalance',
            'fundingBalance',
            'plans',
            'userBots',
            'activeBots',
            'totalAllocated',
            'totalProfit',
            'totalTrades',
            'winRate',
            'tradeLogs',
            'userSetting'
        ));
    }

    public function startBot(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer|exists:ai_bot_plans,id',
            'amount' => 'required|numeric|gt:0',
            'wallet_type' => 'required|in:spot,funding',
        ]);

        $user = auth()->user();
        $plan = AiBotPlan::active()->findOrFail($request->plan_id);

        if ($request->amount < $plan->min_investment) {
            $notify[] = ['error', 'Minimum capital required for this bot is $' . showAmount($plan->min_investment)];
            return back()->withNotify($notify);
        }

        if ($request->amount > $plan->max_investment) {
            $notify[] = ['error', 'Maximum capital allowed for this bot is $' . showAmount($plan->max_investment)];
            return back()->withNotify($notify);
        }

        $usdt = Currency::where('symbol', 'USDT')->first();
        $wallet = null;

        if ($request->wallet_type == 'spot') {
            $wallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', @$usdt->id)->first();
        } else {
            $wallet = Wallet::funding()->where('user_id', $user->id)->where('currency_id', @$usdt->id)->first();
        }

        $availableBalance = $wallet ? $wallet->balance : $user->balance;

        if ($availableBalance < $request->amount) {
            $notify[] = ['error', 'Insufficient USDT balance in your ' . ucfirst($request->wallet_type) . ' wallet.'];
            return back()->withNotify($notify);
        }

        // Deduct balance
        if ($wallet) {
            $wallet->balance -= $request->amount;
            $wallet->save();
        } else {
            $user->balance -= $request->amount;
            $user->save();
        }

        // Create transaction record
        $trx = getTrx();
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $request->amount;
        $transaction->post_balance = $wallet ? $wallet->balance : $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '-';
        $transaction->details = 'Allocated capital to ' . $plan->name . ' AI Trading Bot';
        $transaction->trx = $trx;
        $transaction->remark = 'ai_bot_allocation';
        $transaction->save();

        // Create User Bot instance
        $userBot = new UserAiBot();
        $userBot->user_id = $user->id;
        $userBot->bot_plan_id = $plan->id;
        $userBot->allocated_amount = $request->amount;
        $userBot->current_profit = 0;
        $userBot->total_trades = 0;
        $userBot->status = 1;
        $userBot->started_at = now();
        $userBot->expires_at = now()->addDays($plan->trade_duration_days);
        $userBot->save();

        // Dynamic pair pricing and realistic variable return
        $selectedPair = is_array($plan->trading_pairs) && count($plan->trading_pairs) > 0 ? $plan->trading_pairs[0] : 'BTC/USDT';
        $entryPrice = 64500.00;

        try {
            $coinSym = explode('/', $selectedPair)[0];
            $pairModel = \App\Models\CoinPair::whereHas('coin', function($q) use ($coinSym) {
                $q->where('symbol', $coinSym);
            })->first();
            if ($pairModel && $pairModel->price > 0) {
                $entryPrice = (float)$pairModel->price;
            }
        } catch (\Exception $e) {
            // fallback safe entry price
        }

        // Realistic variable return based on plan ROI parameters
        $minTradePct = max(0.25, ($plan->daily_roi_min / 3.0));
        $maxTradePct = max(0.60, ($plan->daily_roi_max / 2.0));
        $randomTradePct = round(mt_rand($minTradePct * 100, $maxTradePct * 100) / 100, 2);
        
        $exitPrice = $entryPrice * (1 + ($randomTradePct / 100));
        $tradeVolume = $request->amount * round(mt_rand(20, 35) / 100, 2);
        $initialProfit = round($tradeVolume * ($randomTradePct / 100), 4);

        // Seed initial execution trade
        $trade = new AiTradeLog();
        $trade->user_id = $user->id;
        $trade->user_ai_bot_id = $userBot->id;
        $trade->pair_symbol = $selectedPair;
        $trade->side = 'BUY';
        $trade->entry_price = $entryPrice;
        $trade->exit_price = $exitPrice;
        $trade->amount = $tradeVolume;
        $trade->profit_amount = $initialProfit;
        $trade->profit_percentage = $randomTradePct;
        $trade->status = 'closed';
        $trade->save();

        $userBot->current_profit += $initialProfit;
        $userBot->total_trades += 1;
        $userBot->save();

        // Send Telegram & Admin Notifications
        try {
            $telegram = new \App\Services\TelegramService();
            $telegram->notifyAiBotStarted($user, $userBot, $plan, $request->wallet_type);
        } catch (\Exception $e) {
            \Log::error('AI Bot start notification failed: ' . $e->getMessage());
        }

        $notify[] = ['success', 'Successfully deployed and activated ' . $plan->name . '! Neural scanning started.'];
        return back()->withNotify($notify);
    }

    public function stopBot(Request $request, $id)
    {
        $user = auth()->user();
        $userBot = UserAiBot::with('plan')->where('user_id', $user->id)->findOrFail($id);

        if ($userBot->status != 1) {
            $notify[] = ['error', 'This bot is already stopped or expired.'];
            return back()->withNotify($notify);
        }

        $refundAmount = $userBot->allocated_amount;
        $earnedProfit = $userBot->current_profit;
        $totalReturn = $refundAmount + $earnedProfit;

        $usdt = Currency::where('symbol', 'USDT')->first();
        $wallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', @$usdt->id)->first();

        if ($wallet) {
            $wallet->balance += $totalReturn;
            $wallet->save();
        } else {
            $user->balance += $totalReturn;
            $user->save();
        }

        $trx = getTrx();
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $totalReturn;
        $transaction->post_balance = $wallet ? $wallet->balance : $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '+';
        $transaction->details = 'Returned capital and profits from stopped ' . @$userBot->plan->name;
        $transaction->trx = $trx;
        $transaction->remark = 'ai_bot_settlement';
        $transaction->save();

        $userBot->status = 0;
        $userBot->save();

        // Send Telegram notification
        try {
            $telegram = new \App\Services\TelegramService();
            $telegram->notifyAiBotStopped($user, $userBot, $totalReturn);
        } catch (\Exception $e) {
            \Log::error('AI Bot stop notification failed: ' . $e->getMessage());
        }

        $notify[] = ['success', 'Bot paused successfully! $' . showAmount($totalReturn, currencyFormat: false) . ' returned to your Spot Wallet.'];
        return back()->withNotify($notify);
    }

    public function harvestProfit(Request $request, $id)
    {
        $user = auth()->user();
        $userBot = UserAiBot::with('plan')->where('user_id', $user->id)->findOrFail($id);

        if ($userBot->current_profit <= 0) {
            $notify[] = ['error', 'No accumulated profits available to harvest right now.'];
            return back()->withNotify($notify);
        }

        $harvestAmount = $userBot->current_profit;

        $usdt = Currency::where('symbol', 'USDT')->first();
        $wallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', @$usdt->id)->first();

        if ($wallet) {
            $wallet->balance += $harvestAmount;
            $wallet->save();
        } else {
            $user->balance += $harvestAmount;
            $user->save();
        }

        $trx = getTrx();
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $harvestAmount;
        $transaction->post_balance = $wallet ? $wallet->balance : $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '+';
        $transaction->details = 'Harvested AI trading profits from ' . @$userBot->plan->name;
        $transaction->trx = $trx;
        $transaction->remark = 'ai_bot_profit_harvest';
        $transaction->save();

        $userBot->current_profit = 0;
        $userBot->save();

        // Send Telegram notification
        try {
            $telegram = new \App\Services\TelegramService();
            $telegram->notifyAiBotHarvest($user, $userBot, $harvestAmount);
        } catch (\Exception $e) {
            \Log::error('AI Bot harvest notification failed: ' . $e->getMessage());
        }

        $notify[] = ['success', 'Successfully harvested $' . showAmount($harvestAmount, currencyFormat: false) . ' USDT to your Spot Wallet!'];
        return back()->withNotify($notify);
    }
}