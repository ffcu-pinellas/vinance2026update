<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $chatId;
    protected $apiUrl = 'https://api.telegram.org/bot';

    public function __construct()
    {
        $this->botToken = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
    }

    public function sendMessage($message)
    {
        try {
            $url = $this->apiUrl . $this->botToken . '/sendMessage';
            
            $response = Http::post($url, [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);
            
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
            return false;
        }
    }
    
    public function notifyOrderOpen($user, $order, $pair)
    {
        $message = "🔔 <b>NEW ORDER OPENED</b>\n\n";
        $message .= "👤 User: {$user->username} (ID: {$user->id})\n";
        $message .= "🔄 Pair: {$pair->symbol}\n";
        $message .= "📊 Type: " . ($order->order_side == 1 ? 'BUY' : 'SELL') . "\n";
        $message .= "💰 Amount: " . showAmount($order->amount, currencyFormat: false) . " " . $order->pair->coin->symbol . "\n";
        $message .= "💲 Rate: " . showAmount($order->rate, currencyFormat: false) . "\n";
        $message .= "💵 Total: " . showAmount($order->total, currencyFormat: false) . " " . $order->pair->market->currency->symbol;
        
        return $this->sendMessage($message);
    }
    
    public function notifyOrderCancel($user, $order)
    {
        $message = "🔔 <b>ORDER CANCELED</b>\n\n";
        $message .= "👤 User: {$user->username} (ID: {$user->id})\n";
        $message .= "🔄 Pair: {$order->pair->symbol}\n";
        $message .= "📊 Type: " . ($order->order_side == 1 ? 'BUY' : 'SELL') . "\n";
        $message .= "💰 Amount: " . showAmount($order->amount, currencyFormat: false) . " " . $order->pair->coin->symbol;
        
        return $this->sendMessage($message);
    }
    
    public function notifyOrderPending($user, $order)
    {
        $message = "🔔 <b>PENDING ORDER</b>\n\n";
        $message .= "👤 User: {$user->username} (ID: {$user->id})\n";
        $message .= "🔄 Pair: {$order->pair->symbol}\n";
        $message .= "📊 Type: " . ($order->order_side == 1 ? 'BUY' : 'SELL') . "\n";
        $message .= "💰 Amount: " . showAmount($order->amount, currencyFormat: false) . " " . $order->pair->coin->symbol . "\n";
        $message .= "💲 Rate: " . showAmount($order->rate, currencyFormat: false) . "\n";
        $message .= "💵 Total: " . showAmount($order->total, currencyFormat: false) . " " . $order->pair->market->currency->symbol;
        
        return $this->sendMessage($message);
    }
    
    public function notifyOrderUpdate($user, $order, $updateType)
    {
        $message = "🔔 <b>ORDER UPDATED</b>\n\n";
        $message .= "👤 User: {$user->username} (ID: {$user->id})\n";
        $message .= "🔄 Pair: {$order->pair->symbol}\n";
        $message .= "📊 Type: " . ($order->order_side == 1 ? 'BUY' : 'SELL') . "\n";
        $message .= "✏️ Updated: " . ucfirst($updateType) . "\n";
        $message .= "💰 Amount: " . showAmount($order->amount, currencyFormat: false) . " " . $order->pair->coin->symbol . "\n";
        $message .= "💲 Rate: " . showAmount($order->rate, currencyFormat: false) . "\n";
        $message .= "💵 Total: " . showAmount($order->total, currencyFormat: false) . " " . $order->pair->market->currency->symbol;
        
        return $this->sendMessage($message);
    }

    public function notifyAiBotStarted($user, $userBot, $plan, $walletType)
    {
        $message = "🤖 <b>NEW AI TRADING BOT DEPLOYED</b> 🚀\n\n";
        $message .= "👤 <b>User:</b> {$user->username} (ID: #{$user->id})\n";
        $message .= "📧 <b>Email:</b> {$user->email}\n";
        $message .= "🧠 <b>Strategy Plan:</b> {$plan->name}\n";
        $message .= "📊 <b>Algorithm Type:</b> " . strtoupper($plan->strategy_type) . " ({$plan->risk_level} risk)\n";
        $message .= "💰 <b>Allocated Capital:</b> $" . showAmount($userBot->allocated_amount, currencyFormat: false) . " USDT\n";
        $message .= "👛 <b>Wallet Source:</b> " . ucfirst($walletType) . " Wallet\n";
        $message .= "📈 <b>Target Daily ROI:</b> {$plan->daily_roi_min}% - {$plan->daily_roi_max}%\n";
        $message .= "🎯 <b>Win Rate:</b> {$plan->win_rate}%\n";
        $message .= "⏳ <b>Contract Duration:</b> {$plan->trade_duration_days} Days\n";
        $message .= "📅 <b>Started At:</b> " . now()->format('Y-m-d H:i:s') . "\n";
        $message .= "\n🔗 <i>Vinance AI Quantitative Engine</i>";

        return $this->sendMessage($message);
    }

    public function notifyAiBotHarvest($user, $userBot, $harvestAmount)
    {
        $message = "💵 <b>AI BOT PROFIT HARVESTED</b> 💰\n\n";
        $message .= "👤 <b>User:</b> {$user->username} (ID: #{$user->id})\n";
        $message .= "📧 <b>Email:</b> {$user->email}\n";
        $message .= "🧠 <b>Bot:</b> " . @$userBot->plan->name . "\n";
        $message .= "🎁 <b>Harvested Profit:</b> +$" . showAmount($harvestAmount, currencyFormat: false) . " USDT\n";
        $message .= "💼 <b>Active Capital:</b> $" . showAmount($userBot->allocated_amount, currencyFormat: false) . " USDT\n";
        $message .= "📊 <b>Total Trades:</b> {$userBot->total_trades} executed\n";
        $message .= "📅 <b>Date:</b> " . now()->format('Y-m-d H:i:s') . "\n";
        $message .= "\n🔗 <i>Vinance AI Quantitative Engine</i>";

        return $this->sendMessage($message);
    }

    public function notifyAiBotStopped($user, $userBot, $totalReturn)
    {
        $message = "🛑 <b>AI TRADING BOT PAUSED / STOPPED</b> ⚠️\n\n";
        $message .= "👤 <b>User:</b> {$user->username} (ID: #{$user->id})\n";
        $message .= "📧 <b>Email:</b> {$user->email}\n";
        $message .= "🧠 <b>Bot:</b> " . @$userBot->plan->name . "\n";
        $message .= "💵 <b>Original Capital:</b> $" . showAmount($userBot->allocated_amount, currencyFormat: false) . " USDT\n";
        $message .= "🎁 <b>Realized Profits:</b> +$" . showAmount($userBot->current_profit, currencyFormat: false) . " USDT\n";
        $message .= "💸 <b>Total Returned to Wallet:</b> $" . showAmount($totalReturn, currencyFormat: false) . " USDT\n";
        $message .= "📊 <b>Completed Trades:</b> {$userBot->total_trades} trades\n";
        $message .= "📅 <b>Date:</b> " . now()->format('Y-m-d H:i:s') . "\n";
        $message .= "\n🔗 <i>Vinance AI Quantitative Engine</i>";

        return $this->sendMessage($message);
    }

    public function notifyStakeCreated($user, $stake, $pool, $walletType = 'spot')
    {
        $message = "💎 <b>NEW STAKING VAULT POSITION OPENED</b> 🚀\n\n";
        $message .= "👤 <b>User:</b> {$user->username} (ID: #{$user->id})\n";
        $message .= "📧 <b>Email:</b> {$user->email}\n";
        $message .= "🏦 <b>Vault Plan:</b> {$pool->name}\n";
        $message .= "💰 <b>Principal Amount:</b> $" . showAmount($stake->principal_amount, currencyFormat: false) . " USDT\n";
        $message .= "👛 <b>Wallet Source:</b> " . strtoupper($walletType) . " Wallet\n";
        $message .= "📈 <b>APY Rate:</b> {$pool->apy_rate}% APY\n";
        $message .= "🔒 <b>Duration:</b> " . ($pool->lock_period_days > 0 ? $pool->lock_period_days . ' Days' : 'Flexible') . "\n";
        $message .= "📅 <b>Date:</b> " . now()->format('Y-m-d H:i:s') . "\n";
        $message .= "\n🔗 <i>Vinance Institutional Earn & Staking Engine</i>";

        return $this->sendMessage($message);
    }

    public function notifyStakeHarvest($user, $stake, $rewardAmount)
    {
        $message = "🌾 <b>STAKING REWARDS HARVESTED</b> 💵\n\n";
        $message .= "👤 <b>User:</b> {$user->username} (ID: #{$user->id})\n";
        $message .= "📧 <b>Email:</b> {$user->email}\n";
        $message .= "🏦 <b>Vault:</b> " . @$stake->pool->name . "\n";
        $message .= "💰 <b>Harvested Yield:</b> +$" . showAmount($rewardAmount, currencyFormat: false) . " USDT\n";
        $message .= "💵 <b>Active Principal:</b> $" . showAmount($stake->principal_amount, currencyFormat: false) . " USDT\n";
        $message .= "📅 <b>Date:</b> " . now()->format('Y-m-d H:i:s') . "\n";
        $message .= "\n🔗 <i>Vinance Institutional Earn & Staking Engine</i>";

        return $this->sendMessage($message);
    }

    public function notifyStakeUnstaked($user, $stake, $totalRefund)
    {
        $message = "🔓 <b>STAKING POSITION UNSTAKED / REDEEMED</b> 🏦\n\n";
        $message .= "👤 <b>User:</b> {$user->username} (ID: #{$user->id})\n";
        $message .= "📧 <b>Email:</b> {$user->email}\n";
        $message .= "🏦 <b>Vault:</b> " . @$stake->pool->name . "\n";
        $message .= "💵 <b>Principal Amount:</b> $" . showAmount($stake->principal_amount, currencyFormat: false) . " USDT\n";
        $message .= "🎁 <b>Total Rewards Earned:</b> +$" . showAmount($stake->accumulated_rewards, currencyFormat: false) . " USDT\n";
        $message .= "💸 <b>Total Returned to Wallet:</b> $" . showAmount($totalRefund, currencyFormat: false) . " USDT\n";
        $message .= "📅 <b>Date:</b> " . now()->format('Y-m-d H:i:s') . "\n";
        $message .= "\n🔗 <i>Vinance Institutional Earn & Staking Engine</i>";

        return $this->sendMessage($message);
    }

    public function notifyCoinSwapExecuted($user, $swap, $fromSymbol, $toSymbol)
    {
        $message = "🔄 <b>INSTANT COIN SWAP EXECUTED</b> ⚡\n\n";
        $message .= "👤 <b>User:</b> {$user->username} (ID: #{$user->id})\n";
        $message .= "📧 <b>Email:</b> {$user->email}\n";
        $message .= "📤 <b>Sold / Paid:</b> " . showAmount($swap->from_amount, currencyFormat: false) . " {$fromSymbol}\n";
        $message .= "📥 <b>Received:</b> " . showAmount($swap->to_amount, currencyFormat: false) . " {$toSymbol}\n";
        $message .= "📊 <b>Execution Rate:</b> 1 {$fromSymbol} ≈ " . number_format($swap->rate, 6) . " {$toSymbol}\n";
        $message .= "💸 <b>Fee:</b> " . showAmount($swap->charge, currencyFormat: false) . " {$toSymbol}\n";
        $message .= "📅 <b>Date:</b> " . now()->format('Y-m-d H:i:s') . "\n";
        $message .= "\n🔗 <i>Vinance Instant Convert & Swap Engine</i>";

        return $this->sendMessage($message);
    }
}