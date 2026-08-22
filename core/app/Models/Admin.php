<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
     
         use Notifiable;

    // Add this method for Telegram notifications
    public function routeNotificationForTelegram()
    {
        return env('TELEGRAM_CHAT_ID');
    }
    
    protected $hidden = [
        'password', 'remember_token',
    ];

}
