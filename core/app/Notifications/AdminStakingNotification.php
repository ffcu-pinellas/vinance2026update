<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class AdminStakingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;
    protected $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $subject = '';
        $content = '';

        switch ($this->type) {
            case 'stake':
                $subject = 'Vinance-New Staking Activity';
                $content = "User {$this->data['user_email']} has staked {$this->data['amount']} USDT in {$this->data['pool_name']} pool";
                break;
            case 'unstake':
                $subject = 'Unstaking Activity';
                $content = "User {$this->data['user_email']} has unstaked {$this->data['amount']} USDT from {$this->data['pool_name']} pool";
                break;
            case 'compound':
                $subject = 'Rewards Compounded';
                $content = "User {$this->data['user_email']} has compounded {$this->data['amount']} USDT rewards in {$this->data['pool_name']} pool";
                break;
        }

        return (new MailMessage)
            ->subject($subject)
            ->line($content)
            ->line('Please review this activity in the admin panel.');
    }
}