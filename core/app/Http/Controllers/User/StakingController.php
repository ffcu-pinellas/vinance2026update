<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\StakingPool;
use App\Models\Stake;
use App\Models\Wallet;
use App\Models\Currency;
use App\Constants\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\StakingNotification;
use App\Mail\AdminStakingNotification;
use Illuminate\Support\Facades\Log;

class StakingController extends Controller
{
    protected $usdt;
    protected $activeTemplate;

    public function __construct()
    {
        parent::__construct();
        $this->activeTemplate = activeTemplate();
        $this->usdt = Currency::where('symbol', 'USDT')->first();
        if (!$this->usdt) {
            throw new \Exception('USDT currency not configured');
        }
    }

public function index()
{
    $pageTitle = 'Staking';
    $user = auth()->user();
    
    $fundingWallet = Wallet::funding()
        ->where('user_id', $user->id)
        ->where('currency_id', $this->usdt->id)
        ->first();
        
    $spotWallet = Wallet::spot()
        ->where('user_id', $user->id)
        ->where('currency_id', $this->usdt->id)
        ->first();

    // Base query for active stakes
    $activeStakesQuery = Stake::with(['pool.configuration'])
        ->where('user_id', $user->id)
        ->where('status', 'active');

    // Get active stakes and calculate current rewards for each
    $activeStakes = $activeStakesQuery->latest()->paginate(10);
    
    $totalEarnings = 0;
    $totalStaked = 0;
    
    $activeStakes->getCollection()->transform(function ($stake) use (&$totalEarnings, &$totalStaked) {
        $daysStaked = Carbon::parse($stake->start_time)->diffInDays(now());
        
        // Calculate current estimated rewards for this stake
        $estimatedTotalReturn = $this->calculateTotalReturn(
            $stake->principal_amount,
            $stake->pool->apy_rate,
            $daysStaked
        );
        
        $estimatedRewards = $estimatedTotalReturn - $stake->principal_amount;
        $stake->estimated_rewards = $estimatedRewards;
        
        // Add to totals for statistics
        $totalStaked += $stake->principal_amount;
        $totalEarnings += $estimatedRewards;
        
        return $stake;
    });

    // Calculate statistics
    $statistics = [
        'total_staked' => $totalStaked,
        'total_earnings' => $totalEarnings,
        'active_stakes' => (clone $activeStakesQuery)->count(),
        'completed_stakes' => Stake::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count(),
        'highest_apy' => StakingPool::where('is_active', true)
            ->max('apy_rate'),
    ];

    // Get staking pools with their statistics
    $stakingPools = StakingPool::with('configuration')
        ->where('is_active', true)
        ->get()
        ->map(function ($pool) {
            $pool->total_participants = Stake::where('pool_id', $pool->id)
                ->where('status', 'active')
                ->count();
            $pool->total_pool_staked = Stake::where('pool_id', $pool->id)
                ->where('status', 'active')
                ->sum('principal_amount');
            return $pool;
        });

    return view($this->activeTemplate . 'user.staking.index', compact(
        'pageTitle',
        'activeStakes',
        'stakingPools',
        'fundingWallet',
        'spotWallet',
        'statistics'
    ));
}

    public function store(Request $request)
    {
        try {
            $request->validate([
                'pool_id' => 'required|integer|exists:staking_pools,id',
                'principal_amount' => 'required|numeric|gt:0',
            ]);

            $user = auth()->user();
            $pool = StakingPool::with('configuration')->findOrFail($request->pool_id);
            
            if (!$pool->is_active) {
                $notify[] = ['error', 'This staking pool is not active'];
                return back()->withNotify($notify);
            }

            $amount = $request->principal_amount;
            
            if ($amount < $pool->configuration->min_amount) {
                $notify[] = ['error', 'Minimum stake amount is ' . showAmount($pool->configuration->min_amount) . ' USDT'];
                return back()->withNotify($notify);
            }

            if ($amount > $pool->configuration->max_amount) {
                $notify[] = ['error', 'Maximum stake amount is ' . showAmount($pool->configuration->max_amount) . ' USDT'];
                return back()->withNotify($notify);
            }

            $fundingWallet = Wallet::funding()
                ->where('user_id', $user->id)
                ->where('currency_id', $this->usdt->id)
                ->first();
            
            $spotWallet = Wallet::spot()
                ->where('user_id', $user->id)
                ->where('currency_id', $this->usdt->id)
                ->first();

            $fundingBalance = $fundingWallet ? $fundingWallet->balance : 0;
            $spotBalance = $spotWallet ? $spotWallet->balance : 0;
            $totalAvailable = $fundingBalance + $spotBalance;

            if ($totalAvailable < $amount) {
                $notify[] = ['error', 'Insufficient USDT balance'];
                return back()->withNotify($notify);
            }

            DB::beginTransaction();
            try {
                if (!$fundingWallet) {
                    $fundingWallet = new Wallet();
                    $fundingWallet->user_id = $user->id;
                    $fundingWallet->currency_id = $this->usdt->id;
                    $fundingWallet->wallet_type = Status::WALLET_TYPE_FUNDING;
                    $fundingWallet->balance = 0;
                    $fundingWallet->save();
                }

                if (!$spotWallet) {
                    $spotWallet = new Wallet();
                    $spotWallet->user_id = $user->id;
                    $spotWallet->currency_id = $this->usdt->id;
                    $spotWallet->wallet_type = Status::WALLET_TYPE_SPOT;
                    $spotWallet->balance = 0;
                    $spotWallet->save();
                }

                if ($fundingBalance >= $amount) {
                    $fundingWallet->balance -= $amount;
                    $fundingWallet->save();
                } else {
                    $fromFunding = $fundingBalance;
                    $fromSpot = $amount - $fromFunding;
                    
                    $fundingWallet->balance = 0;
                    $fundingWallet->save();
                    
                    $spotWallet->balance -= $fromSpot;
                    $spotWallet->save();
                }

                $stake = new Stake();
                $stake->user_id = $user->id;
                $stake->pool_id = $pool->id;
                $stake->principal_amount = $amount;
                $stake->current_amount = $amount;
                $stake->start_time = now();
                $stake->accumulated_rewards = 0; // Initial rewards are 0
                $stake->is_compound = false;
                $stake->status = 'active';
                $stake->save();

                $pool->total_staked += $amount;
                $pool->total_stakers = Stake::where('pool_id', $pool->id)
                    ->where('status', 'active')
                    ->count();
                $pool->save();

                DB::commit();

                $this->sendEnhancedNotifications([
                    'user' => $user,
                    'amount' => $amount,
                    'pool' => $pool,
                    'action' => 'stake',
                    'stake' => $stake
                ]);

                $notify[] = ['success', 'Successfully staked ' . showAmount($amount) . ' USDT'];
                return back()->withNotify($notify);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Staking Error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                $notify[] = ['error', 'An error occurred while processing your stake'];
                return back()->withNotify($notify);
            }

        } catch (\Exception $e) {
            Log::error('Staking Validation Error: ' . $e->getMessage());
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function unstake(Request $request)
    {
        try {
            $request->validate([
                'stake_id' => 'required|integer|exists:stakes,id'
            ]);

            $user = auth()->user();
            $stake = Stake::with(['pool', 'pool.configuration'])
                ->where('user_id', $user->id)
                ->where('id', $request->stake_id)
                ->where('status', 'active')
                ->firstOrFail();

            if ($stake->pool->type === 'locked') {
                $endDate = Carbon::parse($stake->start_time)->addDays($stake->pool->lock_period_days);
                if ($endDate->isFuture()) {
                    $notify[] = ['error', 'Cannot unstake before lock period ends on ' . $endDate->format('Y-m-d H:i:s')];
                    return back()->withNotify($notify);
                }
            }

            DB::beginTransaction();
            try {
                $fundingWallet = Wallet::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'currency_id' => $this->usdt->id,
                        'wallet_type' => Status::WALLET_TYPE_FUNDING
                    ],
                    ['balance' => 0]
                );

                $daysStaked = Carbon::parse($stake->start_time)->diffInDays(now());
                $totalRewards = $this->calculateTotalReturn(
                    $stake->principal_amount,
                    $stake->pool->apy_rate,
                    $daysStaked
                ) - $stake->principal_amount;
                
                $totalReturn = $stake->principal_amount + $totalRewards;

                $fundingWallet->balance += $totalReturn;
                $fundingWallet->save();

                $stake->status = 'completed';
                $stake->end_time = now();
                $stake->accumulated_rewards = $totalRewards; // Save final rewards amount
                $stake->save();

                $pool = $stake->pool;
                $pool->total_staked -= $stake->principal_amount;
                $pool->total_stakers = Stake::where('pool_id', $pool->id)
                    ->where('status', 'active')
                    ->count();
                $pool->save();

                DB::commit();

                $this->sendEnhancedNotifications([
                    'user' => $user,
                    'amount' => $stake->principal_amount,
                    'pool' => $pool,
                    'action' => 'unstake',
                    'stake' => $stake,
                    'rewards' => $totalRewards
                ]);

                $notify[] = ['success', 'Successfully unstaked ' . showAmount($stake->principal_amount) . ' USDT with ' . showAmount($totalRewards) . ' USDT rewards'];
                return back()->withNotify($notify);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Unstaking Error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                $notify[] = ['error', 'An error occurred while processing your unstake request'];
                return back()->withNotify($notify);
            }

        } catch (\Exception $e) {
            Log::error('Unstaking Validation Error: ' . $e->getMessage());
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function compound(Request $request)
    {
        try {
            $request->validate([
                'stake_id' => 'required|integer|exists:stakes,id'
            ]);

            $user = auth()->user();
            $stake = Stake::with(['pool', 'pool.configuration'])
                ->where('user_id', $user->id)
                ->where('id', $request->stake_id)
                ->where('status', 'active')
                ->firstOrFail();

            // Calculate current rewards
            $daysStaked = Carbon::parse($stake->start_time)->diffInDays(now());
            $estimatedRewards = $this->calculateTotalReturn(
                $stake->principal_amount,
                $stake->pool->apy_rate,
                $daysStaked
            ) - $stake->principal_amount;

            if ($estimatedRewards <= 0) {
                $notify[] = ['error', 'No rewards available to compound'];
                return back()->withNotify($notify);
            }

            DB::beginTransaction();
            try {
                // Add rewards to principal
                $stake->current_amount += $estimatedRewards;
                $stake->principal_amount = $stake->current_amount; // Update principal to include compounded rewards
                $stake->accumulated_rewards = 0;
                $stake->is_compound = true;
                $stake->last_compound_time = now();
                $stake->start_time = now(); // Reset start time for accurate future calculations
                $stake->save();

                $pool = $stake->pool;
                $pool->total_staked += $estimatedRewards;
                $pool->save();

                DB::commit();

                $this->sendEnhancedNotifications([
                    'user' => $user,
                    'amount' => $estimatedRewards,
                    'pool' => $pool,
                    'action' => 'compound',
                    'stake' => $stake
                ]);

                $notify[] = ['success', 'Successfully compounded ' . showAmount($estimatedRewards) . ' USDT rewards'];
                return back()->withNotify($notify);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Compounding Error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                $notify[] = ['error', 'An error occurred while processing your compound request'];
                return back()->withNotify($notify);
            }

        } catch (\Exception $e) {
            Log::error('Compounding Validation Error: ' . $e->getMessage());
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    // Method to update rewards for all active stakes (should be run via cron job)
    public function updateRewards()
    {
        try {
            $activeStakes = Stake::with('pool')
                ->where('status', 'active')
                ->get();

            foreach ($activeStakes as $stake) {
                $daysStaked = Carbon::parse($stake->start_time)->diffInDays(now());
                $currentRewards = $this->calculateTotalReturn(
                    $stake->principal_amount,
                    $stake->pool->apy_rate,
                    $daysStaked
                ) - $stake->principal_amount;
                
                $stake->accumulated_rewards = $currentRewards;
                $stake->save();
            }
            
            Log::info('Staking rewards updated for ' . count($activeStakes) . ' active stakes.');
            return response()->json(['success' => true, 'message' => 'Rewards updated successfully']);
            
        } catch (\Exception $e) {
            Log::error('Update Rewards Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Error updating rewards']);
        }
    }

    protected function calculateDailyReward($amount, $apyRate)
    {
        $dailyRate = $apyRate / 36500;
        return $amount * $dailyRate;
    }

    protected function calculateTotalReturn($amount, $apyRate, $days)
    {
        $dailyRate = $apyRate / 36500;
        return $amount * pow(1.0 + $dailyRate, $days);
    }

    protected function sendEnhancedNotifications($data)
    {
        try {
            $user = $data['user'];
            $amount = $data['amount'];
            $pool = $data['pool'];
            $action = $data['action'];
            $stake = $data['stake'] ?? null;
            $rewards = $data['rewards'] ?? 0;

            $formattedAmount = number_format($amount, 2);
            $formattedRewards = number_format($rewards, 2);
            $currentDate = now()->format('Y-m-d H:i:s');

            // Email Notifications
            $userSubject = '💰 Vinance - ' . ucfirst($action) . ' Confirmation';
            $userContent = "Hello {$user->username},\n\n";
            
            switch ($action) {
                case 'stake':
                    $userContent .= "You have successfully staked {$formattedAmount} USDT in the {$pool->configuration->token_name} pool.\n";
                    $userContent .= "APY Rate: {$pool->apy_rate}%\n";
                    $userContent .= "Estimated Daily Earnings: " . number_format($this->calculateDailyReward($amount, $pool->apy_rate), 2) . " USDT\n";
                    break;
                    
                case 'unstake':
                    $duration = $stake->start_time->diffInDays($stake->end_time);
                    $userContent .= "You have successfully unstaked your funds from the {$pool->configuration->token_name} pool.\n";
                    $userContent .= "Original Stake: {$formattedAmount} USDT\n";
                    $userContent .= "Total Rewards Earned: {$formattedRewards} USDT\n";
                    $userContent .= "Total Received: " . number_format($amount + $rewards, 2) . " USDT\n";
                    $userContent .= "Staking Duration: {$duration} days\n";
                    break;
                    
                case 'compound':
                    $userContent .= "You have successfully compounded {$formattedAmount} USDT rewards in the {$pool->configuration->token_name} pool.\n";
                    $userContent .= "New Staked Amount: " . number_format($stake->current_amount, 2) . " USDT\n";
                    break;
            }
            
            $userContent .= "\nTransaction Date: {$currentDate}\n";
            $userContent .= "\nThank you for using Vinance!\n";

            $adminEmail = env('ADMIN_EMAIL', 'admin@yourdomain.com');
            $adminSubject = '📊 Vinance - New ' . ucfirst($action) . ' Activity';
            $adminContent = "User: {$user->username} ({$user->email})\n";
            $adminContent .= "Action: {$action}\n";
            $adminContent .= "Amount: {$formattedAmount} USDT\n";
            $adminContent .= "Pool: {$pool->configuration->token_name}\n";
            $adminContent .= "APY: {$pool->apy_rate}%\n";
            
            if ($action === 'unstake') {
                $adminContent .= "Rewards: {$formattedRewards} USDT\n";
                $adminContent .= "Duration: " . $stake->start_time->diffInDays($stake->end_time) . " days\n";
            }
            
            $adminContent .= "Date: {$currentDate}\n";

            // Send email notifications
            try {
                Mail::to($user->email)->send(new StakingNotification($userSubject, $userContent));
                
                if (filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($adminEmail)->send(new AdminStakingNotification(
                        $adminSubject,
                        $adminContent,
                        $user->email,
                        $formattedAmount,
                        $pool->configuration->token_name,
                        $pool->apy_rate,
                        $action === 'unstake' ? $formattedRewards : null
                    ));
                }
            } catch (\Exception $e) {
                Log::error('Email sending failed: ' . $e->getMessage());
            }

            // Telegram Notifications
            try {
                $botToken = env('TELEGRAM_BOT_TOKEN');
                $chatId = env('TELEGRAM_CHAT_ID');
                
                if ($botToken && $chatId) {
                    $telegramMessage = "✨ <b>{$userSubject}</b> ✨\n\n";
                    $telegramMessage .= "👤 <b>User:</b> {$user->username} ({$user->email})\n";
                    $telegramMessage .= "📊 <b>Action:</b> " . ucfirst($action) . "\n";
                    $telegramMessage .= "💰 <b>Amount:</b> {$formattedAmount} USDT\n";
                    
                    if ($action === 'unstake') {
                        $telegramMessage .= "🎁 <b>Rewards:</b> {$formattedRewards} USDT\n";
                        $telegramMessage .= "💵 <b>Total Received:</b> " . number_format($amount + $rewards, 2) . " USDT\n";
                        $telegramMessage .= "⏳ <b>Duration:</b> " . $stake->start_time->diffInDays($stake->end_time) . " days\n";
                    }
                    
                    $telegramMessage .= "🏊 <b>Pool:</b> {$pool->configuration->token_name}\n";
                    $telegramMessage .= "📈 <b>APY:</b> {$pool->apy_rate}%\n";
                    $telegramMessage .= "📅 <b>Date:</b> {$currentDate}\n";
                    $telegramMessage .= "\n🔗 <i>This is an automated notification</i>";

                    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
                    $data = [
                        'chat_id' => $chatId,
                        'text' => $telegramMessage,
                        'parse_mode' => 'HTML',
                        'disable_web_page_preview' => true
                    ];
                    
                    $options = [
                        'http' => [
                            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                            'method' => 'POST',
                            'content' => http_build_query($data),
                        ],
                    ];
                    
                    $context = stream_context_create($options);
                    file_get_contents($url, false, $context);
                }
            } catch (\Exception $e) {
                Log::error('Telegram notification failed: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            Log::error('Notification system error: ' . $e->getMessage());
        }
    }
}