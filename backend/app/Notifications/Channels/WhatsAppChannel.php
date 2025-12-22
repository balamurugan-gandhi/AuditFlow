<?php

namespace App\Notifications\Channels;

use App\Models\Setting;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        // Check if client has WhatsApp notifications enabled
        if (!$notifiable->whatsapp_notification_enabled) {
            return;
        }

        if (!method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);
        $to = $notifiable->whatsapp_number ?? $notifiable->phone;

        if (!$to) {
            return;
        }

        // Clean phone number (remove non-digits, ensure country code)
        // This is a basic implementation. Ideally, use a phone number library.
        $to = preg_replace('/\D/', '', $to);

        $settings = Setting::all()->pluck('value', 'key');
        $token = $settings['whatsapp_access_token'] ?? null;
        $phoneId = $settings['whatsapp_phone_number_id'] ?? null;

        if (!$token || !$phoneId) {
            Log::warning('WhatsApp API credentials not found in settings.');
            return;
        }

        try {
            $response = Http::withToken($token)
                ->post("https://graph.facebook.com/v20.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'template',
                    'template' => $message
                ]);

            if (!$response->successful()) {
                Log::error('WhatsApp API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp Channel Exception: ' . $e->getMessage());
        }
    }
}
