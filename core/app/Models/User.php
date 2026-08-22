<?php

namespace App\Models;

use App\Constants\Status;
use App\Traits\UserNotify;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {
    use HasApiTokens, UserNotify, Notifiable;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'ver_code', 'kyc_data',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'kyc_data'          => 'object',
        'ver_code_send_at'  => 'datetime',
        'email_notifications' => 'boolean'
    ];

    public function loginLogs() {
        return $this->hasMany(UserLogin::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class)->orderBy('id', 'desc');
    }

    public function wallets() {
        return $this->hasMany(Wallet::class, 'user_id');
    }

    public function deposits() {
        return $this->hasMany(Deposit::class)->where('status', '!=', Status::PAYMENT_INITIATE);
    }

    public function withdrawals() {
        return $this->hasMany(Withdrawal::class)->where('status', '!=', Status::PAYMENT_INITIATE);
    }

    public function tickets() {
        return $this->hasMany(SupportTicket::class);
    }

    public function fullname(): Attribute {
        return new Attribute(
            get: fn() => $this->firstname . ' ' . $this->lastname,
        );
    }

    public function mobileNumber(): Attribute {
        return new Attribute(
            get: fn() => $this->dial_code . $this->mobile,
        );
    }

    public function referrer() {
        return $this->belongsTo(self::class, 'ref_by');
    }

    public function referrals() {
        return $this->hasMany(self::class, 'ref_by');
    }

    public function activeReferrals() {
        return $this->hasMany(self::class, 'ref_by');
    }

    public function allReferrals() {
        return $this->referrals()->with('referrer');
    }

    // SCOPES
    public function scopeActive($query) {
        return $query->where('status', Status::USER_ACTIVE)->where('ev', Status::VERIFIED)->where('sv', Status::VERIFIED);
    }

    public function scopeBanned($query) {
        return $query->where('status', Status::USER_BAN);
    }

    public function scopeEmailUnverified($query) {
        return $query->where('ev', Status::UNVERIFIED);
    }

    public function scopeMobileUnverified($query) {
        return $query->where('sv', Status::UNVERIFIED);
    }

    public function scopeKycUnverified($query) {
        return $query->where('kv', Status::KYC_UNVERIFIED);
    }

    public function scopeKycPending($query) {
        return $query->where('kv', Status::KYC_PENDING);
    }

    public function scopeEmailVerified($query) {
        return $query->where('ev', Status::VERIFIED);
    }

    public function scopeMobileVerified($query) {
        return $query->where('sv', Status::VERIFIED);
    }

    public function deviceTokens() {
        return $this->hasMany(DeviceToken::class);
    }
    
    public function hasActiveTelegram(): bool
    {
        // Check if table exists first
        if (!\Schema::hasTable('telegram_users')) {
            return false;
        }

        // Check if relationship exists
        if (!method_exists($this, 'telegramUser')) {
            return false;
        }

        // Return connection status
        return $this->telegramUser()->exists();
    }

    public function telegramUser()
    {
        return $this->hasOne(TelegramUser::class, 'user_id');
    }

    public function notifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')
            ->orderBy('created_at', 'desc');
    }

    public function unreadNotifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc');
    }

    public function readNotifications()
    {
        return $this->morphMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable')
            ->whereNotNull('read_at')
            ->orderBy('created_at', 'desc');
    }

    public function markAsRead($notificationId = null)
    {
        if ($notificationId) {
            return $this->unreadNotifications()
                ->where('id', $notificationId)
                ->update(['read_at' => now()]);
        }

        return $this->unreadNotifications()->update(['read_at' => now()]);
    }

    public function markAsUnread($notificationId = null)
    {
        if ($notificationId) {
            return $this->readNotifications()
                ->where('id', $notificationId)
                ->update(['read_at' => null]);
        }

        return $this->readNotifications()->update(['read_at' => null]);
    }

    public function routeNotificationForMail($notification = null)
    {
        return $this->email;
    }

    public function routeNotificationForDatabase($notification = null)
    {
        return $this->id;
    }

    public function routeNotificationForBroadcast($notification = null)
    {
        return $this->id;
    }
}