<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStakingSetting extends Model
{
    protected $table = 'user_staking_settings';

    protected $fillable = [
        'user_id',
        'custom_apy_boost',
        'force_lock_exemption',
        'custom_notes'
    ];

    protected $casts = [
        'custom_apy_boost' => 'decimal:2',
        'force_lock_exemption' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
