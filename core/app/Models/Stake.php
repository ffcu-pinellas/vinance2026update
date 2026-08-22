<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stake extends Model
{
    protected $table = 'stakes';

    protected $fillable = [
        'user_id',
        'pool_id',
        'principal_amount',
        'current_amount',
        'accumulated_rewards',
        'start_time',
        'end_time',
        'is_compound',
        'status',
        'last_compound_time'
    ];

    protected $casts = [
        'principal_amount' => 'decimal:8',
        'current_amount' => 'decimal:8',
        'accumulated_rewards' => 'decimal:8',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'last_compound_time' => 'datetime',
        'is_compound' => 'boolean'
    ];

    public function pool()
    {
        return $this->belongsTo(StakingPool::class, 'pool_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}