<?php

namespace App\Lib;

use Illuminate\Support\Facades\Http;

class Telegram {

    public static function sendMessage(array $data) {
        $botToken = config('services.telegram.bot_token');
        $chatId = config('services.telegram.admin_chat_id');
        
        if (!$botToken || !$chatId) {
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $data['text'],
                'parse_mode' => $data['parse_mode'] ?? 'HTML'
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Log::error("Telegram API error: ".$e->getMessage());
            return false;
        }
    }
}