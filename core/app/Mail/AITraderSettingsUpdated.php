<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AITraderSettingsUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $settings;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $settings)
    {
        $this->user = $user;
        $this->settings = $settings;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.ai-trader-settings-updated')
                   ->subject('AI Trader Settings Updated - Admin Notification');
    }
}