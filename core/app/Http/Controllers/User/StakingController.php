<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Stake;
use App\Models\StakingPool;
use App\Models\Transaction;
use App\Models\UserStakingSetting;
use App\Models\Wallet;
use App\Services\TelegramService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StakingController extends Controller
{
    protected $usdt;

    public function __construct()
    {
        parent::__construct();
        $this->usdt = Currency::where('symbol', 'USDT')->first();
    }

    public function index()
    {
        $pageTitle = 'Crypto Staking & Yield Vaults';
        $user = auth()->user();

        // Fetch balances
        $spotWallet = null;
        $fundingWallet = null;

        if ($this->usdt) {
            $spotWallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', $this->usdt->id)->first();
            $fundingWallet = Wallet::funding()->where('user_id', $user->id)->where('currency_id', $this->usdt->id)->first();
        }

        $spotBalance = $spotWallet ? $spotWallet->balance : $user->balance;
        $fundingBalance = $fundingWallet ? $fundingWallet->balance : 0;

        // User specific overrides
        $userSetting = UserStakingSetting::where('user_id', $user->id)->first();
        $apyBoost = $userSetting && $userSetting->custom_apy_boost ? (float)$userSetting->custom_apy_boost : 0;

        // Active Stakes
        $activeStakes = Stake::with('pool')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->latest()
            ->get();

        // Calculate dynamic real-time earnings on active stakes
        $totalStaked = 0;
        $totalEarnings = 0;

        foreach ($activeStakes as $stake) {
            $pool = $stake->pool;
            if (!$pool) continue;

            $effectiveApy = $pool->apy_rate + $apyBoost;
            $daysElapsed = Carbon::parse($stake->start_time)->diffInSeconds(now()) / 86400;
            
            // Dynamic earnings calculation
            $dynamicEarned = ($stake->principal_amount * ($effectiveApy / 100) / 365) * $daysElapsed;
            $currentTotalRewards = (float)$stake->accumulated_rewards + (float)$dynamicEarned;
            
            $stake->live_rewards = $currentTotalRewards;
            $totalStaked += (float)$stake->principal_amount;
            $totalEarnings += $currentTotalRewards;
        }

        // Completed stakes history
        $stakeHistory = Stake::with('pool')
            ->where('user_id', $user->id)
            ->latest()
            ->take(25)
            ->get();

        // Available Staking Pools
        $pools = StakingPool::active()->orderBy('rank')->get();

        $statistics = [
            'total_staked' => $totalStaked,
            'total_earnings' => $totalEarnings,
            'active_stakes_count' => $activeStakes->count(),
            'best_apy' => $pools->max('apy_rate') ?? 24.20
        ];

        return view('templates.basic.user.staking.index', compact(
            'pageTitle',
            'user',
            'spotBalance',
            'fundingBalance',
            'activeStakes',
            'stakeHistory',
            'pools',
            'statistics',
            'apyBoost'
        ));
    }

    public function stake(Request $request)
    {
        $request->validate([
            'pool_id' => 'required|exists:staking_pools,id',
            'amount' => 'required|numeric|gt:0',
            'wallet_type' => 'required|in:spot,funding'
        ]);

        $user = auth()->user();
        $pool = StakingPool::active()->findOrFail($request->pool_id);
        $amount = (float)$request->amount;

        // Check limits
        if ($pool->min_amount > 0 && $amount < $pool->min_amount) {
            $notify[] = ['error', 'Minimum stake amount for this pool is $' . number_format($pool->min_amount, 2) . ' USDT'];
            return back()->withNotify($notify);
        }

        if ($pool->max_amount > 0 && $amount > $pool->max_amount) {
            $notify[] = ['error', 'Maximum stake amount for this pool is $' . number_format($pool->max_amount, 2) . ' USDT'];
            return back()->withNotify($notify);
        }

        // Wallet Balance Check & Deduction
        $wallet = null;
        if ($this->usdt) {
            if ($request->wallet_type == 'spot') {
                $wallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', $this->usdt->id)->first();
            } else {
                $wallet = Wallet::funding()->where('user_id', $user->id)->where('currency_id', $this->usdt->id)->first();
            }
        }

        $availableBalance = $wallet ? $wallet->balance : ($request->wallet_type == 'spot' ? $user->balance : 0);

        if ($availableBalance < $amount) {
            $notify[] = ['error', 'Insufficient balance in your ' . ucfirst($request->wallet_type) . ' Wallet.'];
            return back()->withNotify($notify);
        }

        DB::beginTransaction();
        try {
            if ($wallet) {
                $wallet->balance -= $amount;
                $wallet->save();
            } else {
                $user->balance -= $amount;
                $user->save();
            }

            // Create Transaction
            $trx = getTrx();
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $amount;
            $transaction->post_balance = $wallet ? $wallet->balance : $user->balance;
            $transaction->charge = 0;
            $transaction->trx_type = '-';
            $transaction->details = 'Staked in ' . $pool->name . ' (' . ($pool->lock_period_days > 0 ? $pool->lock_period_days . ' Days' : 'Flexible') . ')';
            $transaction->trx = $trx;
            $transaction->remark = 'crypto_stake';
            $transaction->save();

            // Create Stake record
            $startTime = now();
            $endTime = $pool->lock_period_days > 0 ? now()->addDays($pool->lock_period_days) : null;

            $stake = new Stake();
            $stake->user_id = $user->id;
            $stake->pool_id = $pool->id;
            $stake->principal_amount = $amount;
            $stake->current_amount = $amount;
            $stake->accumulated_rewards = 0;
            $stake->start_time = $startTime;
            $stake->end_time = $endTime;
            $stake->is_compound = false;
            $stake->status = 'active';
            $stake->save();

            // Update pool stats
            $pool->total_staked += $amount;
            $pool->total_stakers = Stake::where('pool_id', $pool->id)->where('status', 'active')->count();
            $pool->save();

            DB::commit();

            // Send Telegram Notification
            try {
                $telegram = new TelegramService();
                $telegram->notifyStakeCreated($user, $stake, $pool, $request->wallet_type);
            } catch (\Exception $e) {
                Log::error('Staking Telegram notification error: ' . $e->getMessage());
            }

            $notify[] = ['success', 'Successfully staked $' . number_format($amount, 2) . ' USDT in ' . $pool->name . '!'];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            DB::rollBack();
            $notify[] = ['error', 'An error occurred while creating your stake position: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function harvest(Request $request, $id)
    {
        $user = auth()->user();
        $stake = Stake::with('pool')->where('user_id', $user->id)->where('status', 'active')->findOrFail($id);

        $pool = $stake->pool;
        if (!$pool) {
            $notify[] = ['error', 'Staking pool not found'];
            return back()->withNotify($notify);
        }

        // Calculate dynamic reward
        $userSetting = UserStakingSetting::where('user_id', $user->id)->first();
        $apyBoost = $userSetting && $userSetting->custom_apy_boost ? (float)$userSetting->custom_apy_boost : 0;
        $effectiveApy = $pool->apy_rate + $apyBoost;
        $daysElapsed = Carbon::parse($stake->start_time)->diffInSeconds(now()) / 86400;

        $dynamicEarned = ($stake->principal_amount * ($effectiveApy / 100) / 365) * $daysElapsed;
        $harvestAmount = (float)$stake->accumulated_rewards + (float)$dynamicEarned;

        if ($harvestAmount <= 0) {
            $notify[] = ['error', 'No accumulated rewards available to harvest at this time.'];
            return back()->withNotify($notify);
        }

        DB::beginTransaction();
        try {
            $wallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', @$this->usdt->id)->first();

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
            $transaction->details = 'Harvested yield rewards from ' . $pool->name;
            $transaction->trx = $trx;
            $transaction->remark = 'staking_reward_harvest';
            $transaction->save();

            // Reset stake accumulated rewards and update start_time
            $stake->accumulated_rewards = 0;
            $stake->start_time = now();
            $stake->last_compound_time = now();
            $stake->save();

            DB::commit();

            // Send Telegram Notification
            try {
                $telegram = new TelegramService();
                $telegram->notifyStakeHarvest($user, $stake, $harvestAmount);
            } catch (\Exception $e) {
                Log::error('Harvest Telegram notification error: ' . $e->getMessage());
            }

            $notify[] = ['success', 'Successfully harvested +$' . number_format($harvestAmount, 2) . ' USDT to your Spot Wallet!'];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            DB::rollBack();
            $notify[] = ['error', 'Harvest failed: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function unstake(Request $request, $id)
    {
        $user = auth()->user();
        $stake = Stake::with('pool')->where('user_id', $user->id)->where('status', 'active')->findOrFail($id);

        $pool = $stake->pool;
        if (!$pool) {
            $notify[] = ['error', 'Staking pool not found'];
            return back()->withNotify($notify);
        }

        $userSetting = UserStakingSetting::where('user_id', $user->id)->first();
        $isExempt = $userSetting && $userSetting->force_lock_exemption;

        // Check if locked and locked period not ended
        $isLocked = $pool->type == 'locked' && $stake->end_time && now()->lt($stake->end_time);
        $penaltyAmount = 0;

        if ($isLocked && !$isExempt) {
            if ($pool->early_unstake_penalty_percentage > 0) {
                $penaltyAmount = ($stake->principal_amount * ($pool->early_unstake_penalty_percentage / 100));
            }
        }

        // Calculate dynamic reward up to now
        $apyBoost = $userSetting && $userSetting->custom_apy_boost ? (float)$userSetting->custom_apy_boost : 0;
        $effectiveApy = $pool->apy_rate + $apyBoost;
        $daysElapsed = Carbon::parse($stake->start_time)->diffInSeconds(now()) / 86400;

        $dynamicEarned = ($stake->principal_amount * ($effectiveApy / 100) / 365) * $daysElapsed;
        $totalEarned = (float)$stake->accumulated_rewards + (float)$dynamicEarned;

        $totalRefund = ($stake->principal_amount + $totalEarned) - $penaltyAmount;

        DB::beginTransaction();
        try {
            $wallet = Wallet::spot()->where('user_id', $user->id)->where('currency_id', @$this->usdt->id)->first();

            if ($wallet) {
                $wallet->balance += $totalRefund;
                $wallet->save();
            } else {
                $user->balance += $totalRefund;
                $user->save();
            }

            $trx = getTrx();
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount = $totalRefund;
            $transaction->post_balance = $wallet ? $wallet->balance : $user->balance;
            $transaction->charge = $penaltyAmount;
            $transaction->trx_type = '+';
            $transaction->details = 'Redeemed & unstaked principal + rewards from ' . $pool->name . ($penaltyAmount > 0 ? " (Penalty: \${$penaltyAmount})" : '');
            $transaction->trx = $trx;
            $transaction->remark = 'staking_unstake';
            $transaction->save();

            $stake->status = 'completed';
            $stake->accumulated_rewards = $totalEarned;
            $stake->save();

            // Update pool stats
            $pool->total_staked = max(0, $pool->total_staked - $stake->principal_amount);
            $pool->total_stakers = Stake::where('pool_id', $pool->id)->where('status', 'active')->count();
            $pool->save();

            DB::commit();

            // Send Telegram Notification
            try {
                $telegram = new TelegramService();
                $telegram->notifyStakeUnstaked($user, $stake, $totalRefund);
            } catch (\Exception $e) {
                Log::error('Unstake Telegram notification error: ' . $e->getMessage());
            }

            $notify[] = ['success', 'Successfully unstaked and returned $' . number_format($totalRefund, 2) . ' USDT to your Spot Wallet!'];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            DB::rollBack();
            $notify[] = ['error', 'Unstake failed: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }
}