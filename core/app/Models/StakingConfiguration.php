<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StakingConfiguration extends Model
{
    protected $table = 'staking_configurations';

    protected $fillable = [
        'name',
        'min_amount',
        'max_amount',
        'early_unstake_penalty'
    ];

    public function pools()
    {
        return $this->hasMany(StakingPool::class, 'configuration_id');
    }
}