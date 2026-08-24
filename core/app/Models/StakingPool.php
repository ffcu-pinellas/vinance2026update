<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StakingPool extends Model
{
    protected $table = 'staking_pools';
    
    protected $fillable = [
        'configuration_id',
        'name',
        'token_symbol',
        'type',
        'lock_period_days',
        'apy_rate',
        'min_amount',
        'max_amount',
        'early_unstake_penalty_percentage',
        'badge_tag',
        'total_staked',
        'total_stakers',
        'rank',
        'is_active'
    ];

    protected $casts = [
        'apy_rate' => 'decimal:2',
        'min_amount' => 'decimal:8',
        'max_amount' => 'decimal:8',
        'early_unstake_penalty_percentage' => 'decimal:2',
        'total_staked' => 'decimal:8',
        'total_stakers' => 'integer',
        'lock_period_days' => 'integer',
        'rank' => 'integer',
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

    public function calculateProjectedRewards($amount, $days, $customApy = null)
    {
        $apy = $customApy !== null ? $customApy : ($this->apy_rate ?? 0);
        $dailyRate = ($apy / 100) / 365;
        $projected = $amount * pow(1 + $dailyRate, $days) - $amount;
        return round($projected, 8);
    }
    
    public function calculateRewards($amount, $days, $customApy = null)
    {
        $apy = $customApy !== null ? $customApy : ($this->apy_rate ?? 0);
        $dailyRate = ($apy / 100) / 365;
        $rewards = $amount * $dailyRate * $days;
        return round($rewards, 8);
    }
}