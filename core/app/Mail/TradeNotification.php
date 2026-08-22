<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TradeNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $subject = match($this->data['type']) {
            'opened' => 'Vinance-New Binary Trade Opened',
            'won' => 'Vinance-Your Binary Trade Won!',
            'lost' => 'Vinance-Your Binary Trade Completed',
            default => 'Vinance-Your Binary Trade Update'
        };

        return $this->subject($subject)
            ->view('emails.trade_notification')
            ->with([
                'user' => $this->data['user'],
                'trade' => $this->data['trade'],
                'type' => $this->data['type'],
                'entry_price' => $this->data['entry_price'],
                'exit_price' => $this->data['exit_price'],
                'amount' => $this->data['amount'],
                'duration' => $this->data['duration'],
                'direction' => $this->data['direction'],
                'symbol' => $this->data['symbol'],
                'timestamp' => $this->data['timestamp'],
                'trade_id' => $this->data['trade_id'],
                'trx' => $this->data['trx']
            ]);
    }
} 