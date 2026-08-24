<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\AiBotPlan;
use App\Models\AiTradeLog;
use App\Models\User;
use App\Models\UserAiBot;
use App\Models\UserAiSetting;
use Illuminate\Http\Request;

class AiTraderController extends Controller
{
    public function index()
    {
        $pageTitle = 'AI Auto-Trader Overview & Global Management';

        $totalPlans = AiBotPlan::count();
        $activePlans = AiBotPlan::active()->count();
        $totalUserBots = UserAiBot::count();
        $activeUserBots = UserAiBot::where('status', 1)->count();
        $totalCapitalAllocated = UserAiBot::where('status', 1)->sum('allocated_amount');
        $totalProfitGenerated = UserAiBot::sum('current_profit');
        $totalTradesExecuted = AiTradeLog::count();

        $recentUserBots = UserAiBot::with(['user', 'plan'])->latest()->take(10)->get();
        $recentTrades = AiTradeLog::with('user')->latest()->take(10)->get();

        return view('admin.ai_trader.index', compact(
            'pageTitle',
            'totalPlans',
            'activePlans',
            'totalUserBots',
            'activeUserBots',
            'totalCapitalAllocated',
            'totalProfitGenerated',
            'totalTradesExecuted',
            'recentUserBots',
            'recentTrades'
        ));
    }

    public function plans()
    {
        $pageTitle = 'Manage AI Trading Bot Strategies';
        $plans = AiBotPlan::orderBy('rank')->latest()->get();
        return view('admin.ai_trader.plans', compact('pageTitle', 'plans'));
    }

    public function savePlan(Request $request, $id = 0)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'strategy_type' => 'required|in:scalping,breakout,arbitrage,grid,trend',
            'min_investment' => 'required|numeric|gt:0',
            'max_investment' => 'required|numeric|gte:min_investment',
            'daily_roi_min' => 'required|numeric|min:0',
            'daily_roi_max' => 'required|numeric|gte:daily_roi_min',
            'win_rate' => 'required|numeric|between:50,100',
            'risk_level' => 'required|in:low,medium,high',
            'trade_duration_days' => 'required|integer|min:1',
            'rank' => 'nullable|integer',
            'features' => 'nullable|array',
            'trading_pairs' => 'nullable|array',
        ]);

        if ($id) {
            $plan = AiBotPlan::findOrFail($id);
            $message = 'AI Bot strategy updated successfully';
        } else {
            $plan = new AiBotPlan();
            $message = 'AI Bot strategy created successfully';
        }

        $plan->name = $request->name;
        $plan->tagline = $request->tagline;
        $plan->strategy_type = $request->strategy_type;
        $plan->min_investment = $request->min_investment;
        $plan->max_investment = $request->max_investment;
        $plan->daily_roi_min = $request->daily_roi_min;
        $plan->daily_roi_max = $request->daily_roi_max;
        $plan->win_rate = $request->win_rate;
        $plan->risk_level = $request->risk_level;
        $plan->trade_duration_days = $request->trade_duration_days;
        $plan->rank = $request->rank ?? 0;
        $plan->features = $request->features ?? [];
        $plan->trading_pairs = $request->trading_pairs ?? ['BTC/USDT', 'ETH/USDT', 'SOL/USDT'];
        $plan->save();

        $notify[] = ['success', $message];
        return back()->withNotify($notify);
    }

    public function planStatus($id)
    {
        return AiBotPlan::changeStatus($id);
    }

    public function deletePlan($id)
    {
        $plan = AiBotPlan::findOrFail($id);
        $plan->delete();

        $notify[] = ['success', 'AI Bot strategy deleted successfully'];
        return back()->withNotify($notify);
    }

    public function trades()
    {
        $pageTitle = 'AI Trading Execution Logs & Monitor';
        $trades = AiTradeLog::with('user')->latest()->paginate(getPaginate());
        $users = User::active()->orderBy('username')->get();
        $userBots = UserAiBot::with(['user', 'plan'])->where('status', 1)->get();
        return view('admin.ai_trader.trades', compact('pageTitle', 'trades', 'users', 'userBots'));
    }

    public function injectTrade(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'pair_symbol' => 'required|string',
            'side' => 'required|in:BUY,SELL',
            'entry_price' => 'required|numeric|gt:0',
            'exit_price' => 'required|numeric|gt:0',
            'amount' => 'required|numeric|gt:0',
            'profit_amount' => 'required|numeric',
            'profit_percentage' => 'required|numeric',
            'status' => 'required|in:open,closed',
            'user_ai_bot_id' => 'nullable|integer',
            'created_at' => 'nullable|date',
        ]);

        $trade = new AiTradeLog();
        $trade->user_id = $request->user_id;
        $trade->pair_symbol = strtoupper($request->pair_symbol);
        $trade->side = $request->side;
        $trade->entry_price = $request->entry_price;
        $trade->exit_price = $request->exit_price;
        $trade->amount = $request->amount;
        $trade->profit_amount = $request->profit_amount;
        $trade->profit_percentage = $request->profit_percentage;
        $trade->status = $request->status;
        if ($request->created_at) {
            $trade->created_at = $request->created_at;
        }

        // Attach selected or active bot
        $userBot = null;
        if ($request->user_ai_bot_id) {
            $userBot = UserAiBot::find($request->user_ai_bot_id);
        } else {
            $userBot = UserAiBot::where('user_id', $request->user_id)->where('status', 1)->first();
        }

        if ($userBot) {
            $trade->user_ai_bot_id = $userBot->id;
            $userBot->current_profit += $request->profit_amount;
            $userBot->total_trades += 1;
            $userBot->save();
        }

        $trade->save();

        $notify[] = ['success', 'AI Trade successfully injected and credited!'];
        return back()->withNotify($notify);
    }

    public function updateTrade(Request $request, $id)
    {
        $trade = AiTradeLog::findOrFail($id);

        $request->validate([
            'pair_symbol' => 'required|string',
            'side' => 'required|in:BUY,SELL',
            'entry_price' => 'required|numeric|gt:0',
            'exit_price' => 'required|numeric|gt:0',
            'amount' => 'required|numeric|gt:0',
            'profit_amount' => 'required|numeric',
            'profit_percentage' => 'required|numeric',
            'status' => 'required|in:open,closed',
            'created_at' => 'nullable|date',
        ]);

        // Adjust bot current profit diff if attached
        if ($trade->user_ai_bot_id) {
            $userBot = UserAiBot::find($trade->user_ai_bot_id);
            if ($userBot) {
                $profitDiff = $request->profit_amount - $trade->profit_amount;
                $userBot->current_profit += $profitDiff;
                $userBot->save();
            }
        }

        $trade->pair_symbol = strtoupper($request->pair_symbol);
        $trade->side = $request->side;
        $trade->entry_price = $request->entry_price;
        $trade->exit_price = $request->exit_price;
        $trade->amount = $request->amount;
        $trade->profit_amount = $request->profit_amount;
        $trade->profit_percentage = $request->profit_percentage;
        $trade->status = $request->status;
        if ($request->created_at) {
            $trade->created_at = $request->created_at;
        }
        $trade->save();

        $notify[] = ['success', 'AI Trade record successfully updated!'];
        return back()->withNotify($notify);
    }

    public function deleteTrade($id)
    {
        $trade = AiTradeLog::findOrFail($id);
        $trade->delete();

        $notify[] = ['success', 'Trade record deleted successfully'];
        return back()->withNotify($notify);
    }

    public function userAiSettings($userId)
    {
        $user = User::findOrFail($userId);
        $pageTitle = 'User-Specific AI Trader Settings - ' . $user->username;

        $setting = UserAiSetting::where('user_id', $userId)->first();
        $userBots = UserAiBot::with('plan')->where('user_id', $userId)->latest()->get();
        $trades = AiTradeLog::where('user_id', $userId)->latest()->take(20)->get();
        $plans = AiBotPlan::active()->get();

        return view('admin.users.ai_settings', compact('pageTitle', 'user', 'setting', 'userBots', 'trades', 'plans'));
    }

    public function updateUserAiSettings(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $request->validate([
            'custom_win_rate' => 'nullable|numeric|between:0,100',
            'custom_daily_roi_min' => 'nullable|numeric|min:0',
            'custom_daily_roi_max' => 'nullable|numeric|gte:custom_daily_roi_min',
            'force_status' => 'nullable|in:0,1',
            'custom_notes' => 'nullable|string',
        ]);

        $setting = UserAiSetting::firstOrNew(['user_id' => $userId]);
        $setting->custom_win_rate = $request->custom_win_rate;
        $setting->custom_daily_roi_min = $request->custom_daily_roi_min;
        $setting->custom_daily_roi_max = $request->custom_daily_roi_max;
        $setting->force_status = $request->force_status;
        $setting->custom_notes = $request->custom_notes;
        $setting->save();

        $notify[] = ['success', 'User-specific AI settings updated successfully!'];
        return back()->withNotify($notify);
    }
}
