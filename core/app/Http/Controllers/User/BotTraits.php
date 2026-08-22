<?php

namespace App\Http\Controllers\User;

trait BotTraits {
    protected function getBotStats($userId)
    {
        return [
            'total_trades' => 0,
            'active_trades' => 0,
            'completed_trades' => 0,
            'success_rate' => 0,
            'total_profit' => 0,
            'today_profit' => 0,
        ];
    }
}