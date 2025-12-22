<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FileReceived extends Notification
{
    use Queueable;

    protected $file;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function via(object $notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable)
    {
        return [
            'name' => 'file_received', // Template name
            'language' => ['code' => 'en_US'],
            'components' => [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $this->file->file_type],
                        ['type' => 'text', 'text' => $this->file->assessment_year],
                    ]
                ]
            ]
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'file_id' => $this->file->id,
            'message' => "File received: {$this->file->file_type} for {$this->file->assessment_year}",
        ];
    }
}
