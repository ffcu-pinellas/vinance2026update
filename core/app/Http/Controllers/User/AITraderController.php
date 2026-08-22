<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Mail\AITraderSettingsUpdated;
use App\Models\Order; // Added missing import
use App\Models\CoinPair; // Added missing import

class AITraderController extends Controller
{
    // Get trading stats
    public function getTradingStats()
    {
        $user = auth()->user();
        
        return [
            'total_trades' => Order::where('user_id', $user->id)->where('is_bot', 1)->count(),
            'active_trades' => Order::where('user_id', $user->id)->where('is_bot', 1)->where('status', 0)->count(),
            'completed_trades' => Order::where('user_id', $user->id)->where('is_bot', 1)->where('status', 1)->count(),
            'total_profit' => Order::where('user_id', $user->id)->where('is_bot', 1)->where('status', 1)->sum('profit_amount')
        ];
    }
    
    /**
     * Display the AI Trader settings form
     */
    public function settings()
    {
        $settings = DB::table('ai_trader_settings')
                    ->where('user_id', auth()->id())
                    ->first();
                    
        // Initialize default settings if none exist
        if (!$settings) {
            $settings = (object)[
                'risk_level' => 'medium',
                'trading_strategy' => 'breakout',
                'max_trades' => 5,
                'trading_pairs' => json_encode(['btc', 'eth']),
                'telegram_notifications' => true,
                'auto_trade' => true
            ];
        }
        
        return view('templates.basic.user.ai_settings', [
            'settings' => $settings,
            'pageTitle' => 'AI Trader Settings'
        ]);
    }

    /**
     * Save the AI Trader settings
     */
    public function saveSettings(Request $request)
    {
        $validated = $request->validate([
            'risk_level' => 'required|in:low,medium,high',
            'trading_strategy' => 'required|in:trend,breakout,scalping,swing,arbitrage',
            'max_trades' => 'required|integer|min:1|max:100',
            'trading_pairs' => 'required|array|min:1',
            'trading_pairs.*' => 'string|in:btc,eth,bnb,sol,ada,xrp,doge,dot,matic,avax,link,ltc,atom',
            'telegram_notifications' => 'sometimes|boolean',
            'auto_trade' => 'sometimes|boolean'
        ]);

        // Prepare data for storage
        $validated['trading_pairs'] = json_encode($validated['trading_pairs']);
        $validated['telegram_notifications'] = $request->has('telegram_notifications');
        $validated['auto_trade'] = $request->has('auto_trade');

        // Save to database
        DB::table('ai_trader_settings')->updateOrInsert(
            ['user_id' => auth()->id()],
            $validated
        );

        // Send admin notifications (to owner)
        $this->sendAdminNotifications(auth()->user(), $validated);

        return back()->with('success', 'AI Trader settings updated successfully!');
    }

    /**
     * Send notifications to admin/owner
     */
    protected function sendAdminNotifications($user, $settings)
    {
        // Always send Telegram notification to admin
        $this->sendAdminTelegramNotification($user, $settings);
        
        // Always send email notification to admin
        $this->sendAdminEmailNotification($user, $settings);
    }

    /**
     * Send Telegram notification to admin
     */
    protected function sendAdminTelegramNotification($user, $settings)
    {
        $botToken = config('services.telegram.bot_token');
        $adminChatId = config('services.telegram.admin_chat_id');
        
        if (!$botToken || !$adminChatId) {
            Log::error('Admin Telegram notification failed: Missing bot token or chat ID');
            return;
        }

        // Build the message
        $message = "📊 *AI Trader Settings Updated (Admin Alert)*\n\n";
        $message .= "👤 User: {$user->username} ({$user->email})\n";
        $message .= "⚖️ Risk Level: ".ucfirst($settings['risk_level'])."\n";
        $message .= "📈 Strategy: ".ucfirst($settings['trading_strategy'])."\n";
        $message .= "🔢 AI Trading Balance Threshold: {$settings['max_trades']}\n";
        $message .= "📊 Trading Pairs: ".implode(', ', json_decode($settings['trading_pairs']))."\n";
        $message .= "🔔 Telegram Notifications: ".($settings['telegram_notifications'] ? '✅ ON' : '❌ OFF')."\n";
        $message .= "🤖 Auto Trading: ".($settings['auto_trade'] ? '✅ ON' : '❌ OFF');

        try {
            $response = Http::timeout(10)
                ->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                    'chat_id' => $adminChatId,
                    'text' => $message,
                    'parse_mode' => 'Markdown',
                    'disable_notification' => false
                ]);

            if ($response->failed()) {
                $error = $response->json();
                Log::error('Admin Telegram API Error', [
                    'error_code' => $error['error_code'] ?? null,
                    'description' => $error['description'] ?? null,
                    'response' => $error
                ]);
                
                if (isset($error['description']) && str_contains($error['description'], 'chat not found')) {
                    Log::error('Admin Telegram chat not found, please verify the chat ID in config');
                }
            }

        } catch (\Exception $e) {
            Log::error("Admin Telegram notification failed: ".$e->getMessage());
        }
    }

    /**
     * Send email notification to admin
     */
    protected function sendAdminEmailNotification($user, $settings)
    {
        $adminEmail = config('mail.admin_address');
        
        if (!$adminEmail) {
            Log::error('Admin email notification failed: No admin email configured');
            return;
        }

        try {
            Mail::to($adminEmail)
                ->send(new AITraderSettingsUpdated($user, $settings));
                
            Log::info('AI Trader settings update notification sent to admin: '.$adminEmail);
        } catch (\Exception $e) {
            Log::error('Failed to send admin email notification: '.$e->getMessage());
        }
    }
    // Get filtered trades
public function getTrades(Request $request)
{
    $filter = $request->input('filter', 'all');
    $user = $request->user();
    
    $query = Order::with(['pair.coin', 'pair.market.currency'])
        ->where('user_id', $user->id)
        ->where('is_bot', true);
    
    switch ($filter) {
        case 'active':
            $query->where('status', 0);
            break;
        case 'completed':
            $query->where('status', 1);
            break;
    }
    
    $trades = $query->orderBy('created_at', 'desc')
        ->take(10)
        ->get();
    
    return response()->json([
        'table_html' => view('templates.basic.partials.ai_trades_table', ['trades' => $trades])->render(),
        'mobile_html' => view('templates.basic.partials.ai_trades_mobile', ['trades' => $trades])->render()
    ]);
}




}