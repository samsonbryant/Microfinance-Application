<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Welcome to Microfinance MMS!')
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('Welcome to the Microfinance Management System.')
                    ->line('You can now access all the features available to your role.')
                    ->action('Go to Dashboard', url('/dashboard'))
                    ->line('Thank you for using our service!');
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Welcome to Microfinance MMS! You can now access all features.',
            'type' => 'welcome',
            'user_id' => $this->user->id,
        ];
    }
}
