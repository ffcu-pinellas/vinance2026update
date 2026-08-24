<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSwapSetting extends Model
{
    protected $table = 'user_swap_settings';

    protected $fillable = [
        'user_id',
        'custom_fee_percentage',
        'is_swap_locked',
        'custom_notes'
    ];

    protected $casts = [
        'custom_fee_percentage' => 'decimal:2',
        'is_swap_locked' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
