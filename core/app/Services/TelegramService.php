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
}