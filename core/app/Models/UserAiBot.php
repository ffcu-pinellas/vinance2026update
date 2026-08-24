<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAiBot extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(AiBotPlan::class, 'bot_plan_id');
    }

    public function trades()
    {
        return $this->hasMany(AiTradeLog::class, 'user_ai_bot_id');
    }
}
