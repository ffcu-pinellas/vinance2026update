<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StakingReward extends Model
{
    protected $table = 'staking_rewards';

    protected $fillable = [
        'stake_id',
        'reward_amount',
        'type',
        'processed_at',
    ];

    public $timestamps = true; // because your table has created_at and updated_at
}