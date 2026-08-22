<?php

namespace App\Services;

use App\Models\Stake;
use App\Models\StakingRewardHistory;
use App\Models\StakingReward;
use App\Models\StakingReferral;
use Carbon\Carbon;

class StakingRewardService
{
    public function processRewards()
    {
        $activeStakes = Stake::with(['pool', 'user'])
            ->where('status', 'active')
            ->get();

        foreach ($activeStakes as $stake) {
            $this->processStakeRewards($stake);
        }
    }

    private function processStakeRewards(Stake $stake)
    {
        $now = now();
        $lastProcessed = $stake->last_compound_time ?? $stake->start_time;
        $days = $lastProcessed->diffInDays($now);

        if ($days < 1) {
            return;
        }

        // Calculate rewards
        if ($stake->is_compound) {
            $rewards = $stake->pool->calculateProjectedRewards(
                $stake->current_amount,
                $days,
                true
            );
        } else {
            $rewards = $stake->pool->calculateRewards(
                $stake->current_amount,
                $days
            );
        }

        if ($rewards <= 0) {
            return;
        }

        // Record rewards
       // Record rewards
StakingReward::create([
    'stake_id' => $stake->id,
    'reward_amount' => $rewards,
    'type' => 'regular',
    'processed_at' => $now
]);

        // Update stake
        if ($stake->is_compound) {
            $stake->current_amount += $rewards;
            $stake->last_compound_time = $now;
        } else {
            $stake->accumulated_rewards += $rewards;
        }
        $stake->save();

        // Process referral rewards if any
        $this->processReferralRewards($stake, $rewards);
    }

    private function processReferralRewards(Stake $stake, float $rewards)
    {
        $referral = StakingReferral::where('stake_id', $stake->id)
            ->where('status', 'pending')
            ->first();

        if (!$referral) {
            return;
        }

        $referralReward = ($rewards * $referral->reward_percentage) / 100;
        
        if ($referralReward <= 0) {
            return;
        }

        // Update referral record
        $referral->reward_amount += $referralReward;
        $referral->save();

        // Add reward to referrer's balance
        $referrer = $referral->referrer;
        $referrer->balance += $referralReward;
        $referrer->save();

        // Record transaction
        $referrer->transactions()->create([
            'amount' => $referralReward,
            'charge' => 0,
            'post_balance' => $referrer->balance,
            'trx_type' => '+',
            'details' => 'Staking referral reward',
            'trx' => getTrx(),
            'remark' => 'staking_referral'
        ]);
    }
}