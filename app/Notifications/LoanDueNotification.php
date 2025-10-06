<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SmsMessage;
use Illuminate\Notifications\Notification;

class LoanDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $loan;

    /**
     * Create a new notification instance.
     */
    public function __construct(Loan $loan)
    {
        $this->loan = $loan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];
        
        if ($notifiable->email) {
            $channels[] = 'mail';
        }
        
        if ($notifiable->phone) {
            $channels[] = 'sms';
        }
        
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Loan Payment Due Reminder')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('This is a reminder that your loan payment is due.')
            ->line('Loan Details:')
            ->line('Amount: $' . number_format($this->loan->next_payment_amount, 2))
            ->line('Due Date: ' . $this->loan->next_due_date->format('M d, Y'))
            ->line('Outstanding Balance: $' . number_format($this->loan->outstanding_balance, 2))
            ->action('Make Payment', url('/payments/create?loan_id=' . $this->loan->id))
            ->line('Please make your payment on time to avoid penalties.')
            ->salutation('Thank you for your business!');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): SmsMessage
    {
        return (new SmsMessage)
            ->content(
                "Dear {$notifiable->name}, your loan payment of $" . 
                number_format($this->loan->next_payment_amount, 2) . 
                " is due on " . $this->loan->next_due_date->format('M d, Y') . 
                ". Please make payment to avoid penalties."
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'loan_due',
            'loan_id' => $this->loan->id,
            'amount' => $this->loan->next_payment_amount,
            'due_date' => $this->loan->next_due_date->format('Y-m-d'),
            'message' => 'Your loan payment is due',
        ];
    }
}