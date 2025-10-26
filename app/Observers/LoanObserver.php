<?php

namespace App\Observers;

use App\Models\Loan;
use App\Models\Transfer;
use App\Models\RevenueEntry;
use App\Models\ChartOfAccount;
use App\Events\LoanDisbursed;
use App\Events\LoanUpdated;
use App\Events\LoanApplicationSubmitted;
use App\Events\LoanReviewed;
use App\Events\LoanApprovedEvent;

class LoanObserver
{
    /**
     * Handle the Loan "created" event.
     */
    public function created(Loan $loan): void
    {
        // Broadcast when new loan application is submitted
        if ($loan->status === 'pending') {
            broadcast(new LoanApplicationSubmitted($loan))->toOthers();
        }
    }

    /**
     * Handle the Loan "updated" event.
     */
    public function updated(Loan $loan): void
    {
        // Check if loan status changed
        if ($loan->wasChanged('status')) {
            try {
                // Loan moved to under_review (by loan officer)
                if ($loan->status === 'under_review') {
                    broadcast(new LoanReviewed($loan, $loan->reviewed_by ?? auth()->id()))->toOthers();
                }
                
                // Loan approved (by admin)
                if ($loan->status === 'approved') {
                    broadcast(new LoanApprovedEvent($loan))->toOthers();
                }
                
                // Loan disbursed (status changed to 'active' when disbursed)
                if ($loan->status === 'active' && $loan->disbursement_date && $loan->wasChanged('status')) {
                    $this->createDisbursementTransfer($loan);
                    $this->createProcessingFeeRevenue($loan);
                    broadcast(new LoanDisbursed($loan))->toOthers();
                }
            } catch (\Exception $e) {
                \Log::error('LoanObserver error: ' . $e->getMessage(), [
                    'loan_id' => $loan->id,
                    'status' => $loan->status
                ]);
            }
        }
        
        try {
            broadcast(new LoanUpdated($loan))->toOthers();
        } catch (\Exception $e) {
            \Log::error('LoanUpdated broadcast error: ' . $e->getMessage());
        }
    }

    /**
     * Create transfer entry for loan disbursement
     */
    private function createDisbursementTransfer(Loan $loan): void
    {
        try {
            // Get bank account (default to first active bank)
            $bankAccount = ChartOfAccount::where('code', '1100')->first(); // Bank Accounts
            $loanPortfolioAccount = ChartOfAccount::where('code', '1200')->first(); // Loan Portfolio
            
            if (!$bankAccount || !$loanPortfolioAccount) {
                \Log::warning('Chart of Accounts not found for disbursement', [
                    'loan_id' => $loan->id,
                    'bank_account' => $bankAccount ? 'found' : 'missing',
                    'loan_portfolio' => $loanPortfolioAccount ? 'found' : 'missing',
                ]);
                return;
            }
            
            // Safely get client name
            $clientName = 'Client';
            if ($loan->client) {
                $clientName = $loan->client->full_name ?? $loan->client->name ?? 'Client';
            }
            
            // Use withoutEvents to prevent observer loops
            $transfer = Transfer::withoutEvents(function () use ($loan, $bankAccount, $loanPortfolioAccount, $clientName) {
                return Transfer::create([
                    'transfer_number' => Transfer::generateTransferNumber(),
                    'transaction_date' => $loan->disbursement_date ?? now(),
                    'from_account_id' => $bankAccount->id,
                    'to_account_id' => $loanPortfolioAccount->id,
                    'amount' => $loan->amount,
                    'type' => 'disbursement',
                    'reference_number' => $loan->loan_number,
                    'description' => "Loan disbursement for {$clientName} - Loan #{$loan->loan_number}",
                    'branch_id' => $loan->branch_id,
                    'user_id' => $loan->approved_by ?? auth()->id(),
                    'status' => 'posted', // Auto-post disbursements
                ]);
            });
            
            // Auto-post the transfer if it has a post method
            if ($transfer && method_exists($transfer, 'post')) {
                try {
                    $transfer->post();
                } catch (\Exception $e) {
                    \Log::error('Error posting disbursement transfer: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error creating disbursement transfer: ' . $e->getMessage(), [
                'loan_id' => $loan->id
            ]);
        }
    }

    /**
     * Create revenue entry for processing fee (if applicable)
     */
    private function createProcessingFeeRevenue(Loan $loan): void
    {
        try {
            // Check if loan has processing fee relationship
            if (!method_exists($loan, 'fees')) {
                return;
            }
            
            $processingFee = $loan->fees()->where('fee_type', 'processing_fee')->first();
            
            if ($processingFee && $processingFee->amount > 0) {
                $feeAccount = ChartOfAccount::where('code', '4200')->first(); // Processing Fee Income
                
                if (!$feeAccount) {
                    \Log::warning('Processing fee account not found', ['loan_id' => $loan->id]);
                    return;
                }
                
                // Use withoutEvents to prevent observer loops
                $revenue = RevenueEntry::withoutEvents(function () use ($loan, $feeAccount, $processingFee) {
                    return RevenueEntry::create([
                        'revenue_number' => RevenueEntry::generateRevenueNumber(),
                        'transaction_date' => $loan->disbursement_date ?? now(),
                        'account_id' => $feeAccount->id,
                        'revenue_type' => 'processing_fee',
                        'description' => "Processing fee for loan #{$loan->loan_number}",
                        'amount' => $processingFee->amount,
                        'loan_id' => $loan->id,
                        'client_id' => $loan->client_id,
                        'branch_id' => $loan->branch_id,
                        'user_id' => $loan->approved_by ?? auth()->id(),
                        'status' => 'posted',
                    ]);
                });
                
                // Auto-post the revenue if it has a post method
                if ($revenue && method_exists($revenue, 'post')) {
                    try {
                        $revenue->post();
                    } catch (\Exception $e) {
                        \Log::error('Error posting processing fee revenue: ' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error creating processing fee revenue: ' . $e->getMessage(), [
                'loan_id' => $loan->id
            ]);
        }
    }
}

