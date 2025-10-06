<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\Client;
use App\Models\Transaction;
use App\Models\LedgerEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoanService
{
    /**
     * Calculate loan-to-value ratio.
     */
    public function calculateLTV(float $loanAmount, float $collateralValue): float
    {
        if ($collateralValue <= 0) {
            return 0;
        }
        
        return ($loanAmount / $collateralValue) * 100;
    }

    /**
     * Calculate risk score based on client history and loan details.
     */
    public function calculateRiskScore(Client $client, array $loanData): float
    {
        $score = 0;
        
        // Base score
        $score += 50;
        
        // Previous loan history
        $previousLoans = $client->loans()->where('status', '!=', 'pending')->count();
        if ($previousLoans > 0) {
            $defaultedLoans = $client->loans()->where('status', 'defaulted')->count();
            $defaultRate = ($defaultedLoans / $previousLoans) * 100;
            $score += $defaultRate; // Higher default rate = higher risk
        } else {
            $score += 20; // New client penalty
        }
        
        // Loan amount factor
        $loanAmount = $loanData['amount'];
        if ($loanAmount > 100000) {
            $score += 15;
        } elseif ($loanAmount > 50000) {
            $score += 10;
        }
        
        // LTV ratio factor
        $ltv = $this->calculateLTV($loanAmount, $loanData['collateral_value'] ?? 0);
        if ($ltv > 80) {
            $score += 20;
        } elseif ($ltv > 60) {
            $score += 10;
        }
        
        // Client age factor
        $clientAge = $client->created_at->diffInYears(now());
        if ($clientAge < 1) {
            $score += 15;
        } elseif ($clientAge < 2) {
            $score += 10;
        }
        
        return min(100, max(0, $score));
    }

    /**
     * Calculate loan repayment schedule.
     */
    public function calculateRepaymentSchedule(float $principal, float $interestRate, int $termMonths, string $frequency = 'monthly'): array
    {
        $schedule = [];
        $monthlyRate = $interestRate / 100 / 12;
        
        if ($frequency === 'monthly') {
            $payment = $principal * ($monthlyRate * pow(1 + $monthlyRate, $termMonths)) / (pow(1 + $monthlyRate, $termMonths) - 1);
            
            $balance = $principal;
            for ($i = 1; $i <= $termMonths; $i++) {
                $interestPayment = $balance * $monthlyRate;
                $principalPayment = $payment - $interestPayment;
                $balance -= $principalPayment;
                
                $schedule[] = [
                    'installment' => $i,
                    'due_date' => now()->addMonths($i)->format('Y-m-d'),
                    'principal' => round($principalPayment, 2),
                    'interest' => round($interestPayment, 2),
                    'total_payment' => round($payment, 2),
                    'balance' => round(max(0, $balance), 2),
                ];
            }
        }
        
        return $schedule;
    }

    /**
     * Calculate penalty for overdue loans.
     */
    public function calculatePenalty(Loan $loan): float
    {
        if ($loan->status !== 'overdue') {
            return 0;
        }
        
        $overdueDays = now()->diffInDays($loan->next_due_date);
        $penaltyRate = 0.05; // 5% per day
        $penalty = $loan->outstanding_balance * $penaltyRate * $overdueDays;
        
        return round($penalty, 2);
    }

    /**
     * Process loan disbursement.
     */
    public function disburseLoan(Loan $loan): bool
    {
        try {
            DB::beginTransaction();
            
            // Update loan status
            $loan->update([
                'status' => 'disbursed',
                'disbursed_at' => now(),
            ]);
            
            // Create disbursement transaction
            $transaction = Transaction::create([
                'type' => 'disbursement',
                'amount' => $loan->amount,
                'reference_id' => $loan->id,
                'reference_type' => Loan::class,
                'description' => "Loan disbursement for {$loan->client->name}",
                'status' => 'completed',
            ]);
            
            // Create ledger entries
            $this->createLedgerEntries($transaction, $loan);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Process loan repayment.
     */
    public function processRepayment(Loan $loan, float $amount, string $paymentMethod = 'cash'): bool
    {
        try {
            DB::beginTransaction();
            
            // Calculate penalty if overdue
            $penalty = $this->calculatePenalty($loan);
            $totalAmount = $amount + $penalty;
            
            // Create repayment transaction
            $transaction = Transaction::create([
                'type' => 'repayment',
                'amount' => $totalAmount,
                'reference_id' => $loan->id,
                'reference_type' => Loan::class,
                'description' => "Loan repayment for {$loan->client->name}",
                'status' => 'completed',
                'payment_method' => $paymentMethod,
            ]);
            
            // Update loan balance
            $newBalance = $loan->outstanding_balance - $amount;
            $loan->update([
                'outstanding_balance' => max(0, $newBalance),
                'last_payment_date' => now(),
            ]);
            
            // Check if loan is fully paid
            if ($newBalance <= 0) {
                $loan->update(['status' => 'completed']);
            }
            
            // Create ledger entries
            $this->createLedgerEntries($transaction, $loan);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create ledger entries for loan transactions.
     */
    private function createLedgerEntries(Transaction $transaction, Loan $loan): void
    {
        if ($transaction->type === 'disbursement') {
            // Debit: Loan Receivable, Credit: Cash
            LedgerEntry::create([
                'account_type' => 'loan_receivable',
                'debit' => $transaction->amount,
                'credit' => 0,
                'description' => "Loan disbursement - {$loan->client->name}",
                'transaction_id' => $transaction->id,
            ]);
            
            LedgerEntry::create([
                'account_type' => 'cash',
                'debit' => 0,
                'credit' => $transaction->amount,
                'description' => "Loan disbursement - {$loan->client->name}",
                'transaction_id' => $transaction->id,
            ]);
        } elseif ($transaction->type === 'repayment') {
            // Debit: Cash, Credit: Loan Receivable
            LedgerEntry::create([
                'account_type' => 'cash',
                'debit' => $transaction->amount,
                'credit' => 0,
                'description' => "Loan repayment - {$loan->client->name}",
                'transaction_id' => $transaction->id,
            ]);
            
            LedgerEntry::create([
                'account_type' => 'loan_receivable',
                'debit' => 0,
                'credit' => $transaction->amount,
                'description' => "Loan repayment - {$loan->client->name}",
                'transaction_id' => $transaction->id,
            ]);
        }
    }

    /**
     * Check for overdue loans and update their status.
     */
    public function checkOverdueLoans(): int
    {
        $overdueLoans = Loan::where('status', 'disbursed')
            ->where('next_due_date', '<', now())
            ->get();
        
        $count = 0;
        foreach ($overdueLoans as $loan) {
            $loan->update(['status' => 'overdue']);
            $count++;
        }
        
        return $count;
    }
}