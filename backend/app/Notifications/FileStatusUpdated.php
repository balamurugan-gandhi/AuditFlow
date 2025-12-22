<?php

namespace App\Notifications;

use App\Notifications\Channels\WhatsAppChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FileStatusUpdated extends Notification
{
    use Queueable;

    protected $file;
    protected $status;

    public function __construct($file, $status)
    {
        $this->file = $file;
        $this->status = $status;
    }

    public function via(object $notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable)
    {
        // Format status for display (e.g., 'ready-to-file' -> 'Ready To File')
        $displayStatus = ucwords(str_replace('-', ' ', $this->status));

        return [
            'name' => 'file_status_update', // Template name
            'language' => ['code' => 'en_US'],
            'components' => [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $this->file->file_type],
                        ['type' => 'text', 'text' => $displayStatus],
                    ]
                ]
            ]
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'file_id' => $this->file->id,
            'status' => $this->status,
            'message' => "File {$this->file->file_type} status updated to {$this->status}",
        ];
    }
}
