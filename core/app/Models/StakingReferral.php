<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StakingReferral extends Model
{
    protected $fillable = [
        'referrer_id',
        'referred_id',
        'stake_id',
        'reward_percentage',
        'reward_amount',
        'status'
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred()
    {
        return $this->belongsTo(User::class, 'referred_id');
    }

    public function stake()
    {
        return $this->belongsTo(Stake::class);
    }
}