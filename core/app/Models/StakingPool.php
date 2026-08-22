<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StakingPool extends Model
{
    protected $table = 'staking_pools';
    
    protected $fillable = [
        'configuration_id',
        'type',
        'lock_period_days',
        'apy_rate',
        'total_staked',
        'total_stakers',
        'is_active'
    ];

    protected $casts = [
        'apy_rate' => 'decimal:2',
        'total_staked' => 'decimal:8',
        'total_stakers' => 'integer',
        'lock_period_days' => 'integer',
        'is_active' => 'boolean'
    ];

    public function configuration()
    {
        return $this->belongsTo(StakingConfiguration::class, 'configuration_id');
    }

    public function stakes()
    {
        return $this->hasMany(Stake::class, 'pool_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Compound interest
    public function calculateProjectedRewards($amount, $days)
    {
        $apy = $this->apy_rate ?? 0;
        $dailyRate = ($apy / 100) / 365;
        $projected = $amount * pow(1 + $dailyRate, $days) - $amount;
        return round($projected, 8);
    }
    
    // Simple interest
    public function calculateRewards($amount, $days)
    {
        $apy = $this->apy_rate ?? 0;
        $dailyRate = ($apy / 100) / 365;
        $rewards = $amount * $dailyRate * $days;
        return round($rewards, 8);
    }
}