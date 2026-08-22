<?php

namespace App\Http\Controllers;

use App\Models\Stake;
use App\Models\StakingPool;
use App\Models\StakingConfiguration;
use App\Models\StakingRewardHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StakingController extends Controller
{
    public function index()
    {
        $pageTitle = 'Staking Dashboard';
        $user = auth()->user();
        
        $data['stakingPools'] = StakingPool::with('configuration')
            ->where('is_active', true)
            ->get();
            
        $data['userStakes'] = Stake::where('user_id', $user->id)
            ->where('status', 'active')
            ->with(['pool.configuration'])
            ->get();
            
        $data['totalStaked'] = $data['userStakes']->sum('current_amount');
        $data['totalRewards'] = $data['userStakes']->sum(function($stake) {
            return $stake->calculateCurrentRewards();
        });
        
        $data['activeStakes'] = $data['userStakes']->count();
        $data['availableForStaking'] = $user->balance; // Adjust based on your balance system

        return view($this->activeTemplate . 'user.staking.index', $data);
    }

    public function stake(Request $request)
    {
        $request->validate([
            'pool_id' => 'required|exists:staking_pools,id',
            'amount' => 'required|numeric|gt:0',
        ]);

        $user = auth()->user();
        $pool = StakingPool::with('configuration')->findOrFail($request->pool_id);

        // Validate amount against min/max
        if ($request->amount < $pool->configuration->min_amount) {
            $notify[] = ['error', 'Minimum staking amount is ' . getAmount($pool->configuration->min_amount)];
            return back()->withNotify($notify);
        }

        if ($request->amount > $pool->configuration->max_amount) {
            $notify[] = ['error', 'Maximum staking amount is ' . getAmount($pool->configuration->max_amount)];
            return back()->withNotify($notify);
        }

        if ($request->amount > $user->balance) {
            $notify[] = ['error', 'Insufficient balance'];
            return back()->withNotify($notify);
        }

        $stake = new Stake();
        $stake->user_id = $user->id;
        $stake->pool_id = $pool->id;
        $stake->principal_amount = $request->amount;
        $stake->current_amount = $request->amount;
        $stake->start_time = now();
        $stake->end_time = $pool->type === 'locked' ? now()->addDays($pool->lock_period_days) : null;
        $stake->save();

        // Deduct from user balance
        $user->balance -= $request->amount;
        $user->save();

        // Update pool statistics
        $pool->total_staked += $request->amount;
        $pool->total_stakers++;
        $pool->save();

        $notify[] = ['success', 'Staking successful'];
        return back()->withNotify($notify);
    }

    public function unstake(Request $request, Stake $stake)
    {
        if ($stake->user_id !== auth()->id()) {
            $notify[] = ['error', 'Unauthorized action'];
            return back()->withNotify($notify);
        }

        $user = auth()->user();
        $currentRewards = $stake->calculateCurrentRewards();
        $totalReturn = $stake->current_amount + $currentRewards;

        // Check for early unstaking penalty
        if (!$stake->canUnstake()) {
            $penalty = $stake->getEarlyUnstakePenalty();
            $totalReturn -= $penalty;

            // Record penalty
            StakingRewardHistory::create([
                'stake_id' => $stake->id,
                'reward_amount' => -$penalty,
                'type' => 'early_unstake_penalty',
                'processed_at' => now(),
            ]);
        }

        // Record rewards
        if ($currentRewards > 0) {
            StakingRewardHistory::create([
                'stake_id' => $stake->id,
                'reward_amount' => $currentRewards,
                'type' => 'regular',
                'processed_at' => now(),
            ]);
        }

        // Update user balance
        $user->balance += $totalReturn;
        $user->save();

        // Update stake status
        $stake->status = 'completed';
        $stake->accumulated_rewards += $currentRewards;
        $stake->save();

        // Update pool statistics
        $stake->pool->total_staked -= $stake->current_amount;
        $stake->pool->total_stakers--;
        $stake->pool->save();

        $notify[] = ['success', 'Unstaking successful'];
        return back()->withNotify($notify);
    }

    public function compound(Request $request, Stake $stake)
    {
        if ($stake->user_id !== auth()->id()) {
            $notify[] = ['error', 'Unauthorized action'];
            return back()->withNotify($notify);
        }

        if (!$stake->pool->configuration->allows_compound) {
            $notify[] = ['error', 'Compounding not allowed for this pool'];
            return back()->withNotify($notify);
        }

        $currentRewards = $stake->calculateCurrentRewards();
        $compoundAmount = $request->compound_amount;

        if ($compoundAmount > $currentRewards) {
            $notify[] = ['error', 'Invalid compound amount'];
            return back()->withNotify($notify);
        }

        // Add rewards to stake
        $stake->current_amount += $compoundAmount;
        $stake->accumulated_rewards += ($currentRewards - $compoundAmount);
        $stake->last_compound_time = now();
        $stake->save();

        // Record compound action
        StakingRewardHistory::create([
            'stake_id' => $stake->id,
            'reward_amount' => $compoundAmount,
            'type' => 'compound',
            'processed_at' => now(),
        ]);

        $notify[] = ['success', 'Rewards compounded successfully'];
        return back()->withNotify($notify);
    }

    public function calculateRewards(Request $request)
    {
        $amount = $request->amount;
        $days = $request->days;
        $apy = $request->apy;
        $compound = $request->compound == 'true';

        if ($compound) {
            $dailyRate = ($apy / 100) / 365;
            $rewards = $amount * (pow(1 + $dailyRate, $days) - 1);
        } else {
            $rewards = $amount * ($apy / 100) * ($days / 365);
        }

        return response()->json([
            'rewards' => getAmount($rewards),
            'total' => getAmount($amount + $rewards)
        ]);
    }
}