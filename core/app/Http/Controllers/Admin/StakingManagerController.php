<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Stake;
use App\Models\StakingConfiguration;
use App\Models\StakingPool;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserStakingSetting;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StakingManagerController extends Controller
{
    public function index()
    {
        $pageTitle = 'Staking & Yield Vaults Overview';

        $totalStaked = Stake::where('status', 'active')->sum('principal_amount');
        $totalRewards = Stake::sum('accumulated_rewards');
        $activeStakesCount = Stake::where('status', 'active')->count();
        $completedStakesCount = Stake::whereIn('status', ['completed', 'unstaked'])->count();
        $totalStakersCount = Stake::distinct('user_id')->count('user_id');
        $totalPoolsCount = StakingPool::count();

        $recentStakes = Stake::with(['user', 'pool'])->latest()->take(10)->get();
        $topPools = StakingPool::withCount(['stakes' => function($q) {
            $q->where('status', 'active');
        }])->orderByDesc('stakes_count')->take(5)->get();

        return view('admin.staking.index', compact(
            'pageTitle',
            'totalStaked',
            'totalRewards',
            'activeStakesCount',
            'completedStakesCount',
            'totalStakersCount',
            'totalPoolsCount',
            'recentStakes',
            'topPools'
        ));
    }

    public function pools()
    {
        $pageTitle = 'Staking Pools & Yield Plans';
        $pools = StakingPool::withCount(['stakes' => function($q) {
            $q->where('status', 'active');
        }])->orderBy('rank')->latest()->paginate(getPaginate());

        return view('admin.staking.pools', compact('pageTitle', 'pools'));
    }

    public function savePool(Request $request, $id = null)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'token_symbol' => 'required|string|max:20',
            'type' => 'required|in:flexible,locked',
            'lock_period_days' => 'nullable|integer|min:0',
            'apy_rate' => 'required|numeric|between:0.01,999.99',
            'min_amount' => 'required|numeric|gte:0',
            'max_amount' => 'required|numeric|gt:min_amount',
            'early_unstake_penalty_percentage' => 'nullable|numeric|between:0,100',
            'badge_tag' => 'nullable|string|max:50',
            'rank' => 'nullable|integer|min:0'
        ]);

        if ($id) {
            $pool = StakingPool::findOrFail($id);
            $message = 'Staking pool updated successfully';
        } else {
            $pool = new StakingPool();
            $pool->total_staked = 0;
            $pool->total_stakers = 0;
            $pool->is_active = 1;
            $message = 'Staking pool created successfully';
        }

        $pool->name = $request->name;
        $pool->token_symbol = strtoupper($request->token_symbol);
        $pool->type = $request->type;
        $pool->lock_period_days = $request->type == 'locked' ? ($request->lock_period_days ?? 30) : 0;
        $pool->apy_rate = $request->apy_rate;
        $pool->min_amount = $request->min_amount;
        $pool->max_amount = $request->max_amount;
        $pool->early_unstake_penalty_percentage = $request->early_unstake_penalty_percentage ?? 0;
        $pool->badge_tag = $request->badge_tag;
        $pool->rank = $request->rank ?? 0;
        $pool->save();

        $notify[] = ['success', $message];
        return back()->withNotify($notify);
    }

    public function poolStatus($id)
    {
        $pool = StakingPool::findOrFail($id);
        $pool->is_active = !$pool->is_active;
        $pool->save();

        $statusText = $pool->is_active ? 'activated' : 'deactivated';
        $notify[] = ['success', "Staking pool has been {$statusText}"];
        return back()->withNotify($notify);
    }

    public function deletePool($id)
    {
        $pool = StakingPool::withCount(['stakes' => function($q) {
            $q->where('status', 'active');
        }])->findOrFail($id);

        if ($pool->stakes_count > 0) {
            $notify[] = ['error', 'Cannot delete pool with active user stakes. Please deactivate it instead.'];
            return back()->withNotify($notify);
        }

        $pool->delete();
        $notify[] = ['success', 'Staking pool deleted successfully'];
        return back()->withNotify($notify);
    }

    public function stakes(Request $request)
    {
        $pageTitle = 'Stakes & Manual Injector';
        
        $query = Stake::with(['user', 'pool']);

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($u) use ($search) {
                    $u->where('username', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%");
                })->orWhereHas('pool', function($p) use ($search) {
                    $p->where('name', 'like', "%$search%")
                      ->orWhere('token_symbol', 'like', "%$search%");
                });
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $stakes = $query->latest()->paginate(getPaginate());
        $users = User::active()->orderBy('username')->get();
        $pools = StakingPool::active()->orderBy('rank')->get();

        return view('admin.staking.stakes', compact('pageTitle', 'stakes', 'users', 'pools'));
    }

    public function injectStake(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'pool_id' => 'required|exists:staking_pools,id',
            'principal_amount' => 'required|numeric|gt:0',
            'accumulated_rewards' => 'nullable|numeric|gte:0',
            'start_time' => 'nullable|date',
            'deduct_balance' => 'nullable|in:0,1'
        ]);

        $user = User::findOrFail($request->user_id);
        $pool = StakingPool::findOrFail($request->pool_id);
        $amount = (float)$request->principal_amount;

        // Optionally deduct from user balance
        if ($request->deduct_balance == 1) {
            $usdt = Currency::where('symbol', 'USDT')->first();
            $wallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', @$usdt->id)->first();
            if ($wallet && $wallet->balance >= $amount) {
                $wallet->balance -= $amount;
                $wallet->save();
            } elseif ($user->balance >= $amount) {
                $user->balance -= $amount;
                $user->save();
            }
        }

        $startTime = $request->start_time ? Carbon::parse($request->start_time) : now();
        $endTime = $pool->lock_period_days > 0 ? (clone $startTime)->addDays($pool->lock_period_days) : null;

        $stake = new Stake();
        $stake->user_id = $user->id;
        $stake->pool_id = $pool->id;
        $stake->principal_amount = $amount;
        $stake->current_amount = $amount;
        $stake->accumulated_rewards = (float)($request->accumulated_rewards ?? 0);
        $stake->start_time = $startTime;
        $stake->end_time = $endTime;
        $stake->is_compound = false;
        $stake->status = 'active';
        $stake->save();

        $pool->total_staked += $amount;
        $pool->total_stakers = Stake::where('pool_id', $pool->id)->where('status', 'active')->count();
        $pool->save();

        $notify[] = ['success', "Successfully injected {$amount} USDT stake for user {$user->username} into {$pool->name}"];
        return back()->withNotify($notify);
    }

    public function updateStake(Request $request, $id)
    {
        $request->validate([
            'principal_amount' => 'required|numeric|gt:0',
            'accumulated_rewards' => 'required|numeric|gte:0',
            'status' => 'required|in:active,completed,unstaked',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date'
        ]);

        $stake = Stake::findOrFail($id);
        $stake->principal_amount = (float)$request->principal_amount;
        $stake->accumulated_rewards = (float)$request->accumulated_rewards;
        $stake->current_amount = (float)$request->principal_amount;
        $stake->status = $request->status;
        $stake->start_time = Carbon::parse($request->start_time);
        $stake->end_time = $request->end_time ? Carbon::parse($request->end_time) : null;
        $stake->save();

        $notify[] = ['success', 'Stake position updated successfully'];
        return back()->withNotify($notify);
    }

    public function returnStake($id)
    {
        $stake = Stake::with(['user', 'pool'])->findOrFail($id);
        if ($stake->status != 'active') {
            $notify[] = ['error', 'This stake is already completed or unstaked'];
            return back()->withNotify($notify);
        }

        $user = $stake->user;
        $refundAmount = $stake->principal_amount + $stake->accumulated_rewards;

        $usdt = Currency::where('symbol', 'USDT')->first();
        $wallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', @$usdt->id)->first();

        if ($wallet) {
            $wallet->balance += $refundAmount;
            $wallet->save();
        } else {
            $user->balance += $refundAmount;
            $user->save();
        }

        $trx = getTrx();
        $transaction = new Transaction();
        $transaction->user_id = $user->id;
        $transaction->amount = $refundAmount;
        $transaction->post_balance = $wallet ? $wallet->balance : $user->balance;
        $transaction->charge = 0;
        $transaction->trx_type = '+';
        $transaction->details = 'Admin returned stake principal + yield from ' . @$stake->pool->name;
        $transaction->trx = $trx;
        $transaction->remark = 'admin_stake_refund';
        $transaction->save();

        $stake->status = 'completed';
        $stake->save();

        $notify[] = ['success', "Successfully refunded \${$refundAmount} USDT to {$user->username}'s Spot Wallet!"];
        return back()->withNotify($notify);
    }

    public function deleteStake($id)
    {
        $stake = Stake::findOrFail($id);
        $stake->delete();

        $notify[] = ['success', 'Stake record deleted successfully'];
        return back()->withNotify($notify);
    }

    public function userStakingSettings($userId)
    {
        $user = User::findOrFail($userId);
        $pageTitle = 'Staking Settings - ' . $user->username;
        $userSetting = UserStakingSetting::where('user_id', $user->id)->first();
        $userStakes = Stake::with('pool')->where('user_id', $user->id)->latest()->get();

        return view('admin.users.staking_settings', compact('pageTitle', 'user', 'userSetting', 'userStakes'));
    }

    public function updateUserStakingSettings(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $request->validate([
            'custom_apy_boost' => 'nullable|numeric|between:0,100',
            'force_lock_exemption' => 'nullable|in:0,1',
            'custom_notes' => 'nullable|string'
        ]);

        $setting = UserStakingSetting::firstOrNew(['user_id' => $user->id]);
        $setting->custom_apy_boost = $request->custom_apy_boost;
        $setting->force_lock_exemption = $request->force_lock_exemption ?? 0;
        $setting->custom_notes = $request->custom_notes;
        $setting->save();

        $notify[] = ['success', "Staking settings for {$user->username} updated successfully!"];
        return back()->withNotify($notify);
    }
}