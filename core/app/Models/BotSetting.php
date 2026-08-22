<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotSetting extends Model
{
    protected $fillable = [
        'user_id',
        'risk_level',
        'daily_trade_limit',
        'is_active'
    ];
}