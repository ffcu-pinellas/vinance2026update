<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class AiBotPlan extends Model
{
    use GlobalStatus;

    protected $guarded = ['id'];

    protected $casts = [
        'features' => 'array',
        'trading_pairs' => 'array',
    ];

    public function userBots()
    {
        return $this->hasMany(UserAiBot::class, 'bot_plan_id');
    }
}
