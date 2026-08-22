<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Stake;

class NewStakeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $stake;
    protected $isAdmin;

    /**
     * Create a new notification instance.
     */
    public function __construct($stake, $isAdmin = false)
    {
        $this->stake = $stake;
        $this->isAdmin = $isAdmin;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->isAdmin ? 'New Stake Created by User' : 'Your Stake Has Been Created')
            ->greeting($this->isAdmin ? 'Hello Admin,' : 'Hello ' . $notifiable->username . ',');

        if ($this->isAdmin) {
            $message->line('A new stake has been created by a user.')
                ->line('User: ' . $this->stake->user->username)
                ->line('Email: ' . $this->stake->user->email);
        } else {
            $message->line('Your stake has been successfully created.');
        }

        return $message
            ->line('Stake Details:')
            ->line('Amount: ' . showAmount($this->stake->principal_amount) . ' USDT')
            ->line('Duration: ' . $this->stake->pool->duration . ' days')
            ->line('ROI: ' . $this->stake->pool->interest_rate . '%')
            ->line('Start Time: ' . showDateTime($this->stake->start_time))
            ->line('End Time: ' . showDateTime($this->stake->end_time))
            ->line('Thank you for using our platform!')
            ->action('View Stake', url('/user/staking'));
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