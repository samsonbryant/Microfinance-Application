<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SmsMessage;
use Illuminate\Notifications\Notification;

class LoanApprovedNotification extends Notification implements ShouldQueue
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
            ->subject('Congratulations! Your Loan Has Been Approved')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Congratulations! Your loan application has been approved.')
            ->line('Loan Details:')
            ->line('Amount: $' . number_format($this->loan->amount, 2))
            ->line('Interest Rate: ' . $this->loan->interest_rate . '%')
            ->line('Term: ' . $this->loan->term_months . ' months')
            ->line('Monthly Payment: $' . number_format($this->loan->next_payment_amount, 2))
            ->line('First Payment Due: ' . $this->loan->next_due_date->format('M d, Y'))
            ->action('View Loan Details', url('/loans/' . $this->loan->id))
            ->line('You will be contacted shortly for disbursement arrangements.')
            ->line('Thank you for choosing our microfinance services!')
            ->salutation('Best regards,');
    }

    /**
     * Get the SMS representation of the notification.
     */
    public function toSms(object $notifiable): SmsMessage
    {
        return (new SmsMessage)
            ->content(
                "Congratulations! Your loan application for $" . 
                number_format($this->loan->amount, 2) . 
                " has been approved. You will be contacted for disbursement."
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
            'type' => 'loan_approved',
            'loan_id' => $this->loan->id,
            'amount' => $this->loan->amount,
            'interest_rate' => $this->loan->interest_rate,
            'term_months' => $this->loan->term_months,
            'message' => 'Your loan application has been approved',
        ];
    }
}