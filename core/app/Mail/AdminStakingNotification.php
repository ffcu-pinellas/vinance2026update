<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminStakingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $content;
    public $user_email;
    public $amount;
    public $pool_name;
    public $apy_rate;

    public function __construct($subject, $content, $user_email, $amount, $pool_name, $apy_rate)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->user_email = $user_email;
        $this->amount = $amount;
        $this->pool_name = $pool_name;
        $this->apy_rate = $apy_rate;
    }

    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.admin_staking_notification')
                    ->text('emails.admin_staking_notification_text');
    }
}