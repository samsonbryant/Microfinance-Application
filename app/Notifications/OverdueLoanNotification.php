<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SmsMessage;
use Illuminate\Notifications\Notification;

class OverdueLoanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $loan;
    protected $penalty;

    /**
     * Create a new notification instance.
     */
    public function __construct(Loan $loan, float $penalty = 0)
    {
        $this->loan = $loan;
        $this->penalty = $penalty;
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
        $overdueDays = now()->diffInDays($this->loan->next_due_date);
        
        return (new MailMessage)
            ->subject('URGENT: Loan Payment Overdue')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('URGENT: Your loan payment is overdue!')
            ->line('Loan Details:')
            ->line('Amount: $' . number_format($this->loan->outstanding_balance, 2))
            ->line('Due Date: ' . $this->loan->next_due_date->format('M d, Y'))
            ->line('Days Overdue: ' . $overdueDays)
            ->line('Penalty: $' . number_format($this->penalty, 2))
            ->line('Total Amount Due: $' . number_format($this->loan->outstanding_balance + $this->penalty, 2))
            ->action('Make Payment Now', url('/payments/create?loan_id=' . $this->loan->id))
            ->line('Please contact us immediately to discuss payment arrangements.')
            ->salutation('Thank you for your immediate attention to this matter.');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): SmsMessage
    {
        $overdueDays = now()->diffInDays($this->loan->next_due_date);
        
        return (new SmsMessage)
            ->content(
                "URGENT: Your loan payment is overdue by {$overdueDays} days. " .
                "Amount: $" . number_format($this->loan->outstanding_balance, 2) . 
                " + Penalty: $" . number_format($this->penalty, 2) . 
                ". Please contact us immediately."
            );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $overdueDays = now()->diffInDays($this->loan->next_due_date);
        
        return [
            'type' => 'loan_overdue',
            'loan_id' => $this->loan->id,
            'amount' => $this->loan->outstanding_balance,
            'penalty' => $this->penalty,
            'overdue_days' => $overdueDays,
            'due_date' => $this->loan->next_due_date->format('Y-m-d'),
            'message' => 'Your loan payment is overdue',
        ];
    }
}