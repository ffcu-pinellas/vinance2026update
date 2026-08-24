<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiTradeLog extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userBot()
    {
        return $this->belongsTo(UserAiBot::class, 'user_ai_bot_id');
    }
}
