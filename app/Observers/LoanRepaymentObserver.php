<?php

namespace App\Observers;

use App\Models\LoanRepayment;
use App\Models\RevenueEntry;
use App\Models\ChartOfAccount;
use App\Events\PaymentProcessed;

class LoanRepaymentObserver
{
    /**
     * Handle the LoanRepayment "created" event.
     */
    public function created(LoanRepayment $repayment): void
    {
        // Auto-create revenue entries for interest and penalties
        $this->createRevenueEntries($repayment);
        
        // Broadcast payment processed event
        broadcast(new PaymentProcessed($repayment))->toOthers();
    }

    /**
     * Create revenue entries for loan repayment
     */
    private function createRevenueEntries(LoanRepayment $repayment): void
    {
        $loan = $repayment->loan;
        
        // Create revenue entry for interest if any
        if ($repayment->interest_amount > 0) {
            $interestAccount = ChartOfAccount::where('code', '4000')->first(); // Loan Interest Income
            
            if ($interestAccount) {
                $revenue = RevenueEntry::create([
                    'revenue_number' => RevenueEntry::generateRevenueNumber(),
                    'transaction_date' => $repayment->payment_date ?? now(),
                    'account_id' => $interestAccount->id,
                    'revenue_type' => 'interest_received',
                    'description' => "Interest payment for loan #{$loan->loan_number} - Repayment #{$repayment->id}",
                    'amount' => $repayment->interest_amount,
                    'loan_id' => $loan->id,
                    'client_id' => $loan->client_id,
                    'branch_id' => $loan->branch_id ?? auth()->user()->branch_id,
                    'user_id' => $repayment->collected_by ?? auth()->id(),
                    'status' => 'posted', // Auto-post for repayments
                ]);
                
                // Auto-post the revenue
                try {
                    $revenue->post();
                } catch (\Exception $e) {
                    \Log::error('Error posting interest revenue: ' . $e->getMessage());
                }
            }
        }
        
        // Create revenue entry for penalty if any
        if (isset($repayment->penalty_amount) && $repayment->penalty_amount > 0) {
            $penaltyAccount = ChartOfAccount::where('code', '4100')->first(); // Penalty Income
            
            if ($penaltyAccount) {
                $revenue = RevenueEntry::create([
                    'revenue_number' => RevenueEntry::generateRevenueNumber(),
                    'transaction_date' => $repayment->payment_date ?? now(),
                    'account_id' => $penaltyAccount->id,
                    'revenue_type' => 'default_charges',
                    'description' => "Penalty payment for loan #{$loan->loan_number} - Repayment #{$repayment->id}",
                    'amount' => $repayment->penalty_amount,
                    'loan_id' => $loan->id,
                    'client_id' => $loan->client_id,
                    'branch_id' => $loan->branch_id ?? auth()->user()->branch_id,
                    'user_id' => $repayment->collected_by ?? auth()->id(),
                    'status' => 'posted',
                ]);
                
                // Auto-post the revenue
                try {
                    $revenue->post();
                } catch (\Exception $e) {
                    \Log::error('Error posting penalty revenue: ' . $e->getMessage());
                }
            }
        }
    }
}

