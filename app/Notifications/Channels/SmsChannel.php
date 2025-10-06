<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    protected $twilio;

    public function __construct()
    {
        $this->twilio = new TwilioClient(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /**
     * Send the given notification.
     */
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toSms')) {
            return;
        }

        $phone = $notifiable->phone ?? $notifiable->phone_number;
        if (!$phone) {
            return;
        }

        $message = $notification->toSms($notifiable);

        try {
            $this->twilio->messages->create($phone, [
                'from' => config('services.twilio.from'),
                'body' => $message
            ]);

            Log::info("SMS sent to {$phone}: {$message}");
        } catch (\Exception $e) {
            Log::error("Failed to send SMS to {$phone}: " . $e->getMessage());
        }
    }
}
