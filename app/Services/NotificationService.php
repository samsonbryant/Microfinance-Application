<?php

namespace App\Services;

use App\Models\User;
use App\Models\Loan;
use App\Models\Client;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LoanDueNotification;
use App\Notifications\OverdueLoanNotification;
use App\Notifications\LoanApprovedNotification;
use App\Notifications\PaymentReceivedNotification;

class NotificationService
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
     * Send SMS notification.
     */
    public function sendSMS(string $to, string $message): bool
    {
        try {
            $this->twilio->messages->create($to, [
                'from' => config('services.twilio.from'),
                'body' => $message
            ]);
            
            Log::info("SMS sent to {$to}: {$message}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to send SMS to {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email notification.
     */
    public function sendEmail(string $to, string $subject, string $message, array $data = []): bool
    {
        try {
            Mail::send('emails.notification', [
                'message' => $message,
                'data' => $data
            ], function ($mail) use ($to, $subject) {
                $mail->to($to)->subject($subject);
            });
            
            Log::info("Email sent to {$to}: {$subject}");
            return true;
            
        } catch (\Exception $e) {
            Log::error("Failed to send email to {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify loan due.
     */
    public function notifyLoanDue(Loan $loan): void
    {
        $client = $loan->client;
        $user = $client->user;
        
        if ($user && $user->phone) {
            $message = "Dear {$client->name}, your loan payment of $" . 
                      number_format($loan->next_payment_amount, 2) . 
                      " is due on " . $loan->next_due_date->format('M d, Y') . 
                      ". Please make payment to avoid penalties.";
            
            $this->sendSMS($user->phone, $message);
        }
        
        if ($user && $user->email) {
            $user->notify(new LoanDueNotification($loan));
        }
    }

    /**
     * Notify overdue loan.
     */
    public function notifyOverdueLoan(Loan $loan): void
    {
        $client = $loan->client;
        $user = $client->user;
        
        $penalty = app(LoanService::class)->calculatePenalty($loan);
        
        if ($user && $user->phone) {
            $message = "URGENT: Your loan payment is overdue. Amount: $" . 
                      number_format($loan->outstanding_balance, 2) . 
                      " + Penalty: $" . number_format($penalty, 2) . 
                      ". Please contact us immediately.";
            
            $this->sendSMS($user->phone, $message);
        }
        
        if ($user && $user->email) {
            $user->notify(new OverdueLoanNotification($loan, $penalty));
        }
        
        // Notify loan officer
        if ($loan->loanOfficer) {
            $this->sendEmail(
                $loan->loanOfficer->email,
                'Overdue Loan Alert',
                "Loan #{$loan->id} for {$client->name} is overdue. Amount: $" . 
                number_format($loan->outstanding_balance, 2)
            );
        }
    }

    /**
     * Notify loan approval.
     */
    public function notifyLoanApproval(Loan $loan): void
    {
        $client = $loan->client;
        $user = $client->user;
        
        if ($user && $user->phone) {
            $message = "Congratulations! Your loan application for $" . 
                      number_format($loan->amount, 2) . 
                      " has been approved. You will be contacted for disbursement.";
            
            $this->sendSMS($user->phone, $message);
        }
        
        if ($user && $user->email) {
            $user->notify(new LoanApprovedNotification($loan));
        }
    }

    /**
     * Notify payment received.
     */
    public function notifyPaymentReceived(Loan $loan, float $amount): void
    {
        $client = $loan->client;
        $user = $client->user;
        
        if ($user && $user->phone) {
            $message = "Payment received: $" . number_format($amount, 2) . 
                      " for loan #{$loan->id}. Outstanding balance: $" . 
                      number_format($loan->outstanding_balance, 2);
            
            $this->sendSMS($user->phone, $message);
        }
        
        if ($user && $user->email) {
            $user->notify(new PaymentReceivedNotification($loan, $amount));
        }
    }

    /**
     * Send bulk notifications to all users with overdue loans.
     */
    public function sendBulkOverdueNotifications(): int
    {
        $overdueLoans = Loan::where('status', 'overdue')
            ->with(['client.user'])
            ->get();
        
        $count = 0;
        foreach ($overdueLoans as $loan) {
            $this->notifyOverdueLoan($loan);
            $count++;
        }
        
        return $count;
    }

    /**
     * Send daily due loan reminders.
     */
    public function sendDailyDueReminders(): int
    {
        $dueLoans = Loan::where('status', 'disbursed')
            ->whereDate('next_due_date', now()->addDay())
            ->with(['client.user'])
            ->get();
        
        $count = 0;
        foreach ($dueLoans as $loan) {
            $this->notifyLoanDue($loan);
            $count++;
        }
        
        return $count;
    }

    /**
     * Send system notifications to staff.
     */
    public function notifyStaff(string $message, array $roles = []): void
    {
        $query = User::query();
        
        if (!empty($roles)) {
            $query->whereHas('roles', function ($q) use ($roles) {
                $q->whereIn('name', $roles);
            });
        }
        
        $staff = $query->get();
        
        foreach ($staff as $user) {
            if ($user->email) {
                $this->sendEmail(
                    $user->email,
                    'System Notification',
                    $message
                );
            }
        }
    }
}