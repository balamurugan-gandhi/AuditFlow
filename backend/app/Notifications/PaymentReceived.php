<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceived extends Notification
{
    use Queueable;

    protected $payment;
    protected $invoice;

    /**
     * Create a new notification instance.
     */
    public function __construct($payment, $invoice)
    {
        $this->payment = $payment;
        $this->invoice = $invoice;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', \App\Notifications\Channels\WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable)
    {
        return [
            'name' => 'payment_received', // Template name
            'language' => ['code' => 'en_US'],
            'components' => [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => number_format($this->payment->amount, 2)],
                        ['type' => 'text', 'text' => $this->invoice->invoice_number],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->payment->amount,
            'message' => "Payment of {$this->payment->amount} received for Invoice #{$this->invoice->invoice_number}.",
        ];
    }
}
