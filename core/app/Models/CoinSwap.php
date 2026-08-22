<?php

namespace App\Models;

use App\Constants\Status;
use Illuminate\Database\Eloquent\Model;

class CoinSwap extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'from_amount' => 'decimal:8',
        'to_amount' => 'decimal:8',
        'rate' => 'decimal:8',
        'charge' => 'decimal:8',
        'status' => 'integer',
        'wallet_type' => 'integer', // Add this line
    ];

    // Add these constants for wallet types
    const WALLET_TYPE_SPOT = 1;
    const WALLET_TYPE_FUNDING = 2;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    public function toCurrency()
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', Status::PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', Status::COMPLETED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', Status::REJECTED);
    }

    // Add these scopes for wallet types
    public function scopeSpot($query)
    {
        return $query->where('wallet_type', self::WALLET_TYPE_SPOT);
    }

    public function scopeFunding($query)
    {
        return $query->where('wallet_type', self::WALLET_TYPE_FUNDING);
    }

    // Add this method to get wallet type as string
    public function getWalletTypeStringAttribute()
    {
        return $this->wallet_type == self::WALLET_TYPE_SPOT ? 'spot' : 'funding';
    }

    // Add this method to set wallet type from string
    public function setWalletTypeAttribute($value)
    {
        $this->attributes['wallet_type'] = strtolower($value) === 'spot' ? self::WALLET_TYPE_SPOT : self::WALLET_TYPE_FUNDING;
    }
}