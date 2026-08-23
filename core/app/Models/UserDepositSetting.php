<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDepositSetting extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gatewayCurrency()
    {
        return $this->belongsTo(GatewayCurrency::class, 'gateway_currency_id');
    }

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }
}
