<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class UserStakingNotification extends Notification implements ShouldQueue
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
                $subject = 'Vinance-Staking Confirmation';
                $content = "You have staked {$this->data['amount']} USDT in {$this->data['pool_name']} pool (APY: {$this->data['apy']}%)";
                break;
            case 'unstake':
                $subject = 'Unstaking Confirmation';
                $content = "You have unstaked {$this->data['amount']} USDT from {$this->data['pool_name']} pool";
                break;
            case 'compound':
                $subject = 'Rewards Compounded';
                $content = "You have compounded {$this->data['amount']} USDT rewards in {$this->data['pool_name']} pool";
                break;
        }

        return (new MailMessage)
            ->subject($subject)
            ->line($content)
            ->line('Thank you for using our staking service!');
    }
}