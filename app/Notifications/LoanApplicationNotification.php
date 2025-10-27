<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Loan;

class LoanApplicationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $loan;
    protected $action;

    /**
     * Create a new notification instance.
     */
    public function __construct(Loan $loan, $action = 'submitted')
    {
        $this->loan = $loan;
        $this->action = $action;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $message = new MailMessage();
        
        switch ($this->action) {
            case 'submitted':
                return $message
                    ->subject('New Loan Application - ' . $this->loan->loan_number)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('A new loan application has been submitted.')
                    ->line('Loan Number: ' . $this->loan->loan_number)
                    ->line('Amount: $' . number_format($this->loan->amount, 2))
                    ->line('Applicant: ' . ($this->loan->client->full_name ?? 'N/A'))
                    ->action('Review Application', url('/loans/' . $this->loan->id))
                    ->line('Please review this application at your earliest convenience.');
                    
            case 'documents_added':
                return $message
                    ->subject('Documents Added - ' . $this->loan->loan_number)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('Required documents have been added to your loan application.')
                    ->line('Loan Number: ' . $this->loan->loan_number)
                    ->line('The application is being processed.')
                    ->action('View Application', url('/borrower/loans'))
                    ->line('You will receive updates as your application progresses.');
                    
            case 'kyc_verified':
                return $message
                    ->subject('KYC Verified - ' . $this->loan->loan_number)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('Your KYC documents have been verified by the Branch Manager.')
                    ->line('Loan Number: ' . $this->loan->loan_number)
                    ->line('Your application is now being forwarded for final approval.')
                    ->action('Track Application', url('/borrower/loans'))
                    ->line('Thank you for your patience!');
                    
            case 'approved':
                return $message
                    ->subject('Loan Approved! - ' . $this->loan->loan_number)
                    ->greeting('Congratulations ' . $notifiable->name . '!')
                    ->line('Your loan application has been approved!')
                    ->line('Loan Number: ' . $this->loan->loan_number)
                    ->line('Approved Amount: $' . number_format($this->loan->amount, 2))
                    ->line('Monthly Payment: $' . number_format($this->loan->monthly_payment ?? 0, 2))
                    ->action('View Loan Details', url('/borrower/loans'))
                    ->line('Funds will be disbursed shortly.');
                    
            case 'disbursed':
                return $message
                    ->subject('Loan Disbursed - ' . $this->loan->loan_number)
                    ->greeting('Hello ' . $notifiable->name . '!')
                    ->line('Your loan has been disbursed!')
                    ->line('Loan Number: ' . $this->loan->loan_number)
                    ->line('Amount: $' . number_format($this->loan->amount, 2))
                    ->line('First Payment Due: ' . ($this->loan->next_due_date ? $this->loan->next_due_date->format('M d, Y') : 'TBD'))
                    ->action('View Repayment Schedule', url('/borrower/loans'))
                    ->line('Thank you for choosing us!');
                    
            case 'rejected':
                return $message
                    ->subject('Loan Application Status - ' . $this->loan->loan_number)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('We regret to inform you that your loan application was not approved.')
                    ->line('Loan Number: ' . $this->loan->loan_number)
                    ->line('You may contact your branch for more information or reapply after addressing the concerns.')
                    ->line('Thank you for your understanding.');
                    
            default:
                return $message
                    ->subject('Loan Update - ' . $this->loan->loan_number)
                    ->line('Your loan application has been updated.')
                    ->action('View Loan', url('/borrower/loans'));
        }
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        $messages = [
            'submitted' => 'New loan application submitted for review.',
            'documents_added' => 'Required documents have been added to your application.',
            'kyc_verified' => 'Your KYC documents have been verified.',
            'approved' => 'Your loan application has been approved!',
            'disbursed' => 'Your loan has been disbursed!',
            'rejected' => 'Your loan application was not approved.',
        ];

        return [
            'loan_id' => $this->loan->id,
            'loan_number' => $this->loan->loan_number,
            'amount' => $this->loan->amount,
            'action' => $this->action,
            'message' => $messages[$this->action] ?? 'Loan application updated.',
            'status' => $this->loan->status,
            'url' => $notifiable->hasRole('borrower') ? '/borrower/loans' : '/loans/' . $this->loan->id,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'loan_id' => $this->loan->id,
            'loan_number' => $this->loan->loan_number,
            'action' => $this->action,
            'message' => 'Loan application updated',
        ]);
    }
}

