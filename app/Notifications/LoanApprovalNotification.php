<?php

namespace App\Notifications;

use App\Models\Loan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanApprovalNotification extends Notification implements ShouldQueue
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
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Loan Application - ' . $this->loan->loan_number)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new loan application has been submitted and requires your review.')
            ->line('**Loan Details:**')
            ->line('Application Number: ' . $this->loan->loan_number)
            ->line('Amount: $' . number_format($this->loan->amount, 2))
            ->line('Client: ' . ($this->loan->client->first_name ?? 'N/A') . ' ' . ($this->loan->client->last_name ?? ''))
            ->line('Purpose: ' . $this->loan->loan_purpose)
            ->line('Term: ' . $this->loan->loan_term . ' months')
            ->action('Review Application', url('/loan-applications/' . $this->loan->id))
            ->line('Please review this application at your earliest convenience.')
            ->line('Thank you!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'loan_application_submitted',
            'loan_id' => $this->loan->id,
            'loan_number' => $this->loan->loan_number,
            'amount' => $this->loan->amount,
            'client_name' => ($this->loan->client->first_name ?? 'N/A') . ' ' . ($this->loan->client->last_name ?? ''),
            'message' => 'New loan application submitted for review',
            'url' => '/loan-applications/' . $this->loan->id
        ];
    }
}
