<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWithdrawSetting extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function method()
    {
        return $this->belongsTo(WithdrawMethod::class, 'withdraw_method_id');
    }

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }
}
