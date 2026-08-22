<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Notifications\Notification;
use App\Models\Stake;

class TelegramStakeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $stake;

    /**
     * Create a new notification instance.
     */
    public function __construct($stake)
    {
        $this->stake = $stake;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [TelegramChannel::class];
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable)
    {
        $text = "🔥 *New Stake Created*\n\n"
            . "👤 User: {$this->stake->user->username}\n"
            . "📧 Email: {$this->stake->user->email}\n"
            . "💰 Amount: " . showAmount($this->stake->principal_amount) . " USDT\n"
            . "⏱ Duration: {$this->stake->pool->duration} days\n"
            . "📈 ROI: {$this->stake->pool->interest_rate}%\n"
            . "🕒 Start Time: " . showDateTime($this->stake->start_time) . "\n"
            . "🔚 End Time: " . showDateTime($this->stake->end_time) . "\n\n"
            . "💼 Platform: " . systemDetails()['name'];

        return TelegramMessage::create()
            ->content($text)
            ->parse_mode('Markdown');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'stake_id' => $this->stake->id,
            'amount' => $this->stake->principal_amount,
            'duration' => $this->stake->pool->duration,
            'roi' => $this->stake->pool->interest_rate,
        ];
    }
}