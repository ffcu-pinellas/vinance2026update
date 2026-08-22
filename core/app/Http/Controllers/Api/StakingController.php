<?php

namespace App\Http\Controllers\Api;

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
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use Pusher\Pusher;

class StakingController extends Controller
{
    protected $usdt;

    public function __construct()
    {
        $this->usdt = Currency::where('symbol', 'USDT')->first();
        if (!$this->usdt) {
            throw new \Exception('USDT currency not configured');
        }
    }

    public function index()
    {
        try {
            $user = auth()->user();

            $fundingWallet = Wallet::funding()
                ->where('user_id', $user->id)
                ->where('currency_id', $this->usdt->id)
                ->first();

            $spotWallet = Wallet::spot()
                ->where('user_id', $user->id)
                ->where('currency_id', $this->usdt->id)
                ->first();

            $activeStakesQuery = Stake::with(['pool.configuration'])
                ->where('user_id', $user->id)
                ->where('status', 'active');

            $activeStakes = $activeStakesQuery->latest()->paginate(10);

            $totalEarnings = 0;
            $totalStaked = 0;

            $activeStakes->getCollection()->transform(function ($stake) use (&$totalEarnings, &$totalStaked) {
                $daysStaked = Carbon::parse($stake->start_time)->diffInDays(now());

                // Add formatted dates
                $stake->formatted_start_time = Carbon::parse($stake->start_time)->toDateTimeString();
                if ($stake->end_time) {
                    $stake->formatted_end_time = Carbon::parse($stake->end_time)->toDateTimeString();
                }

                $estimatedTotalReturn = $this->calculateTotalReturn(
                    $stake->principal_amount,
                    $stake->pool->apy_rate,
                    $daysStaked
                );

                $estimatedRewards = $estimatedTotalReturn - $stake->principal_amount;
                $stake->estimated_rewards = $estimatedRewards;

                $totalStaked += $stake->principal_amount;
                $totalEarnings += $estimatedRewards;

                return $stake;
            });

            $stakingHistory = Stake::where('user_id', $user->id)
                ->with(['pool.configuration'])
                ->latest()
                ->paginate(10);

            // Format dates in history
            $stakingHistory->getCollection()->transform(function ($stake) {
                $stake->formatted_start_time = Carbon::parse($stake->start_time)->toDateTimeString();
                if ($stake->end_time) {
                    $stake->formatted_end_time = Carbon::parse($stake->end_time)->toDateTimeString();
                }
                return $stake;
            });

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

            return response()->json([
                'status' => 'success',
                'data' => [
                    'wallets' => [
                        'funding' => $fundingWallet,
                        'spot' => $spotWallet
                    ],
                    'active_stakes' => $activeStakes,
                    'staking_history' => $stakingHistory,
                    'staking_pools' => $stakingPools,
                    'statistics' => $statistics
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch staking data',
                'error' => $e->getMessage()
            ], 500);
        }
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
                return response()->json([
                    'status' => 'error',
                    'message' => 'This staking pool is not active'
                ], 400);
            }

            $amount = $request->principal_amount;

            if ($amount < $pool->configuration->min_amount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Minimum stake amount is ' . number_format($pool->configuration->min_amount, 2) . ' USDT'
                ], 400);
            }

            if ($amount > $pool->configuration->max_amount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Maximum stake amount is ' . number_format($pool->configuration->max_amount, 2) . ' USDT'
                ], 400);
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
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient USDT balance'
                ], 400);
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
                    $this->createTransaction($user, $fundingWallet, $amount, '-', 'Staked in ' . $pool->configuration->token_name . ' pool');
                } else {
                    $fromFunding = $fundingBalance;
                    $fromSpot = $amount - $fromFunding;

                    $fundingWallet->balance = 0;
                    $fundingWallet->save();
                    $this->createTransaction($user, $fundingWallet, $fromFunding, '-', 'Staked in ' . $pool->configuration->token_name . ' pool');

                    $spotWallet->balance -= $fromSpot;
                    $spotWallet->save();
                    $this->createTransaction($user, $spotWallet, $fromSpot, '-', 'Staked in ' . $pool->configuration->token_name . ' pool');
                }

                $stake = new Stake();
                $stake->user_id = $user->id;
                $stake->pool_id = $pool->id;
                $stake->principal_amount = $amount;
                $stake->current_amount = $amount;
                $stake->start_time = now();
                $stake->accumulated_rewards = 0;
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

                $this->sendStakingNotification($user, 'STAKING_STARTED', [
                    'amount' => number_format($amount, 2),
                    'currency' => 'USDT',
                    'pool_name' => $pool->configuration->token_name,
                    'apy' => $pool->apy_rate,
                    'stake_id' => $stake->id
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully staked ' . number_format($amount, 4) . ' USDT',
                    'data' => [
                        'stake_id' => $stake->id,
                        'amount' => $amount,
                        'pool_id' => $pool->id
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Staking Error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                return response()->json([
                    'status' => 'error',
                    'message' => 'An error occurred while processing your stake'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Staking Validation Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
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
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cannot unstake before lock period ends on ' . $endDate->format('Y-m-d H:i:s')
                    ], 400);
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

                $this->createTransaction($user, $fundingWallet, $stake->principal_amount, '+', 'Unstaked principal from ' . $stake->pool->configuration->token_name . ' pool');

                if ($totalRewards > 0) {
                    $this->createTransaction($user, $fundingWallet, $totalRewards, '+', 'Rewards from ' . $stake->pool->configuration->token_name . ' pool');
                }

                $stake->status = 'completed';
                $stake->end_time = now();
                $stake->accumulated_rewards = $totalRewards;
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

                $this->sendStakingNotification($user, 'STAKING_COMPLETED', [
                    'principal' => number_format($stake->principal_amount, 2),
                    'rewards' => number_format($totalRewards, 2),
                    'total' => number_format($totalReturn, 2),
                    'currency' => 'USDT',
                    'pool_name' => $pool->configuration->token_name,
                    'duration' => $daysStaked,
                    'stake_id' => $stake->id
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully unstaked ' . number_format($stake->principal_amount, 2) . ' USDT with ' . number_format($totalRewards, 4) . ' USDT rewards',
                    'data' => [
                        'stake_id' => $stake->id,
                        'principal_amount' => $stake->principal_amount,
                        'rewards' => $totalRewards,
                        'total_return' => $totalReturn
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Unstaking Error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                return response()->json([
                    'status' => 'error',
                    'message' => 'An error occurred while processing your unstake request'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Unstaking Validation Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
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

            $daysStaked = Carbon::parse($stake->start_time)->diffInDays(now());
            $estimatedRewards = $this->calculateTotalReturn(
                $stake->principal_amount,
                $stake->pool->apy_rate,
                $daysStaked
            ) - $stake->principal_amount;

            if ($estimatedRewards <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No rewards available to compound'
                ], 400);
            }

            DB::beginTransaction();
            try {
                $stake->current_amount += $estimatedRewards;
                $stake->principal_amount = $stake->current_amount;
                $stake->accumulated_rewards = 0;
                $stake->is_compound = true;
                $stake->last_compound_time = now();
                $stake->start_time = now();
                $stake->save();

                $pool = $stake->pool;
                $pool->total_staked += $estimatedRewards;
                $pool->save();

                $this->createTransaction($user, null, $estimatedRewards, '+', 'Compounded rewards in ' . $pool->configuration->token_name . ' pool', 'staking_compound');

                DB::commit();

                $this->sendEnhancedNotifications([
                    'user' => $user,
                    'amount' => $estimatedRewards,
                    'pool' => $pool,
                    'action' => 'compound',
                    'stake' => $stake
                ]);

                $this->sendStakingNotification($user, 'STAKING_COMPOUNDED', [
                    'amount' => number_format($estimatedRewards, 4),
                    'currency' => 'USDT',
                    'pool_name' => $pool->configuration->token_name,
                    'new_principal' => number_format($stake->current_amount, 4),
                    'stake_id' => $stake->id
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Successfully compounded ' . number_format($estimatedRewards, 4) . ' USDT rewards',
                    'data' => [
                        'stake_id' => $stake->id,
                        'compounded_amount' => $stake->current_amount,
                        'rewards_compounded' => $estimatedRewards
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Compounding Error: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                return response()->json([
                    'status' => 'error',
                    'message' => 'An error occurred while processing your compound request'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Compounding Validation Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function history(Request $request)
{
    try {
        $user = auth()->user();

        $stakingHistory = Stake::where('user_id', $user->id)
            ->with(['pool.configuration'])
            ->latest()
            ->paginate(10);

        // Format dates in history
        $stakingHistory->getCollection()->transform(function ($stake) {
            $stake->formatted_start_time = Carbon::parse($stake->start_time)->format('d M Y, h:i A');
            if ($stake->end_time) {
                $stake->formatted_end_time = Carbon::parse($stake->end_time)->format('d M Y, h:i A');
            }
            return $stake;
        });

        $transactions = Transaction::where('user_id', $user->id)
            ->where('remark', 'like', 'staking%')
            ->with(['wallet.currency', 'stake.pool.configuration'])
            ->latest()
            ->paginate(10);

        // Fix transaction display issues
        $transactions->getCollection()->transform(function ($transaction) {
            // Format date consistently
            $transaction->formatted_date = Carbon::parse($transaction->created_at)->format('d M Y, h:i A');
            
            // Handle token name for different transaction types
            if ($transaction->remark === 'staking_compound') {
                $transaction->token_name = 'Tether USDt'; // Force USDT for compounding
                // Calculate proper post balance for compounding
                if ($transaction->wallet) {
                    $transaction->post_balance = $transaction->wallet->balance;
                } else {
                    // If no wallet, show the compounded amount as post balance
                    $transaction->post_balance = $transaction->amount;
                }
            } elseif ($transaction->wallet && $transaction->wallet->currency) {
                $transaction->token_name = $transaction->wallet->currency->symbol;
                if ($transaction->wallet->currency->symbol === 'USDT') {
                    $transaction->token_name = 'Tether USDt'; // Use full name for USDT
                }
            }
            
            return $transaction;
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'staking_history' => $stakingHistory,
                'transactions' => $transactions
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch staking history',
            'error' => $e->getMessage()
        ], 500);
    }
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

            $formattedAmount = number_format($amount, 4);
            $formattedRewards = number_format($rewards, 4);
            $currentDate = now()->format('Y-m-d H:i:s');

            $userSubject = '💰 Vinance - ' . ucfirst($action) . ' Confirmation';
            $userContent = "Hello {$user->username},\n\n";

            switch ($action) {
                case 'stake':
                    $userContent .= "You have successfully staked {$formattedAmount} USDT in the {$pool->configuration->token_name} pool.\n";
                    $userContent .= "APY Rate: {$pool->apy_rate}%\n";
                    $userContent .= "Estimated Daily Earnings: " . number_format($this->calculateDailyReward($amount, $pool->apy_rate), 4) . " USDT\n";
                    break;

                case 'unstake':
                    $duration = $stake->start_time->diffInDays($stake->end_time);
                    $userContent .= "You have successfully unstaked your funds from the {$pool->configuration->token_name} pool.\n";
                    $userContent .= "Original Stake: {$formattedAmount} USDT\n";
                    $userContent .= "Total Rewards Earned: {$formattedRewards} USDT\n";
                    $userContent .= "Total Received: " . number_format($amount + $rewards, 4) . " USDT\n";
                    $userContent .= "Staking Duration: {$duration} days\n";
                    break;

                case 'compound':
                    $userContent .= "You have successfully compounded {$formattedAmount} USDT rewards in the {$pool->configuration->token_name} pool.\n";
                    $userContent .= "New Staked Amount: " . number_format($stake->current_amount, 4) . " USDT\n";
                    break;
            }

            $userContent .= "\nTransaction Date: {$currentDate}\n";
            $userContent .= "\nThank you for using Vinance!\n";

            $adminEmail = env('ADMIN_EMAIL', 'support@vinance.pro');
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
                        $telegramMessage .= "💵 <b>Total Received:</b> " . number_format($amount + $rewards, 4) . " USDT\n";
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

    protected function sendStakingNotification($user, $type, $data)
    {
        try {
            $template = NotificationTemplate::where('act', $type)->first();

            if (!$template || !$template->push_status) {
                Log::warning("Notification template not found or disabled for type: {$type}");
                return;
            }

            $title = $template->push_title;
            $message = $template->push_body;

            foreach ($data as $key => $value) {
                $title = str_replace('{{' . $key . '}}', $value, $title);
                $message = str_replace('{{' . $key . '}}', $value, $message);
            }

            $notification = new Notification();
            $notification->user_id = $user->id;
            $notification->title = $title;
            $notification->message = $message;
            $notification->type = $type;
            $notification->save();

            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'useTLS' => true
                ]
            );

            $pusher->trigger(
                'user.' . $user->id,
                'staking-notification',
                [
                    'title' => $title,
                    'message' => $message,
                    'type' => $type,
                    'created_at' => now()->toDateTimeString(),
                    'foreground' => true
                ]
            );

            Log::info("Staking notification sent to user {$user->id}: {$title} - {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send staking notification: " . $e->getMessage());
        }
    }

    private function createTransaction($user, $wallet, $amount, $type, $remark, $remarkType = 'staking')
    {
        try {
            $transaction = new Transaction();
            $transaction->user_id = $user->id;

            if ($wallet) {
                $transaction->wallet_id = $wallet->id;
                $transaction->post_balance = $wallet->balance;
            }

            $transaction->amount = $amount;
            $transaction->charge = 0;
            $transaction->trx_type = $type;
            $transaction->details = $remark;
            $transaction->trx = getTrx();
            $transaction->remark = $remarkType;
            $transaction->save();

            return $transaction;
        } catch (\Exception $e) {
            Log::error('Create transaction error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function calculateDailyReward($principal, $apy)
    {
        $dailyRate = $apy / 365 / 100;
        return $principal * $dailyRate;
    }

    private function calculateTotalReturn($principal, $apy, $days)
    {
        $dailyRate = $apy / 365 / 100;
        return $principal * pow(1 + $dailyRate, $days);
    }
}