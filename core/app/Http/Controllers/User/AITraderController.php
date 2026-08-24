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

        // Seed an immediate initial execution trade
        $trade = new AiTradeLog();
        $trade->user_id = $user->id;
        $trade->user_ai_bot_id = $userBot->id;
        $trade->pair_symbol = 'BTC/USDT';
        $trade->side = 'BUY';
        $trade->entry_price = 64250.00;
        $trade->exit_price = 65480.00;
        $trade->amount = $request->amount * 0.25;
        $initialProfit = round($request->amount * 0.008, 4);
        $trade->profit_amount = $initialProfit;
        $trade->profit_percentage = 1.91;
        $trade->status = 'closed';
        $trade->save();

        $userBot->current_profit += $initialProfit;
        $userBot->total_trades += 1;
        $userBot->save();

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

        $notify[] = ['success', 'Bot paused successfully! $' . showAmount($totalReturn) . ' returned to your Spot Wallet.'];
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

        $notify[] = ['success', 'Successfully harvested $' . showAmount($harvestAmount) . ' USDT to your Spot Wallet!'];
        return back()->withNotify($notify);
    }
}