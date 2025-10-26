<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\KycDocument;
use App\Models\Collateral;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalCenterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin')->except(['myApprovals']);
    }

    /**
     * Unified approval center for admin
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        
        // Get all pending items
        $pendingLoans = Loan::where('status', 'pending')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $pendingSavings = SavingsAccount::where('status', 'pending')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $pendingKycDocs = KycDocument::where('verification_status', 'pending')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $pendingCollateral = Collateral::where('status', 'pending')
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $pendingClients = Client::where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Compile stats
        $stats = [
            'total_pending' => $pendingLoans->count() + $pendingSavings->count() + 
                              $pendingKycDocs->count() + $pendingCollateral->count() + 
                              $pendingClients->count(),
            'pending_loans' => $pendingLoans->count(),
            'pending_savings' => $pendingSavings->count(),
            'pending_kyc' => $pendingKycDocs->count(),
            'pending_collateral' => $pendingCollateral->count(),
            'pending_clients' => $pendingClients->count(),
        ];
        
        return view('approvals.center', compact(
            'pendingLoans',
            'pendingSavings', 
            'pendingKycDocs',
            'pendingCollateral',
            'pendingClients',
            'stats',
            'filter'
        ));
    }

    /**
     * Get real-time approval counts
     */
    public function getStats()
    {
        return response()->json([
            'success' => true,
            'stats' => [
                'pending_loans' => Loan::where('status', 'pending')->count(),
                'pending_savings' => SavingsAccount::where('status', 'pending')->count(),
                'pending_kyc' => KycDocument::where('verification_status', 'pending')->count(),
                'pending_collateral' => Collateral::where('status', 'pending')->count(),
                'pending_clients' => Client::where('status', 'pending')->count(),
            ]
        ]);
    }

    /**
     * Quick approve loan
     */
    public function approveLoan(Request $request, $loanId)
    {
        try {
            $loan = Loan::findOrFail($loanId);
            
            // Use existing loan approval logic
            $calculationService = app(\App\Services\LoanCalculationService::class);
            $schedule = $calculationService->calculateAmortizationSchedule(
                $loan->amount,
                $loan->interest_rate,
                $loan->term_months,
                now()
            );
            
            $updateData = [
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'monthly_payment' => $schedule['monthly_payment'],
                'total_interest' => $schedule['total_interest'],
                'total_amount' => $schedule['total_amount'],
                'outstanding_balance' => $schedule['total_amount'],
                'repayment_schedule' => json_encode($schedule['schedule']),
                'next_payment_amount' => $schedule['monthly_payment'],
            ];
            
            if (!empty($schedule['schedule']) && isset($schedule['schedule'][0]['due_date'])) {
                $updateData['next_due_date'] = $schedule['schedule'][0]['due_date'];
            }
            
            $loan->update($updateData);
            $loan->refresh();
            
            activity()
                ->performedOn($loan)
                ->causedBy(auth()->user())
                ->log("Loan approved via Approval Center: {$loan->loan_number}");
            
            return response()->json([
                'success' => true,
                'message' => 'Loan approved successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Approval Center - Loan approval error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error approving loan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick reject loan
     */
    public function rejectLoan(Request $request, $loanId)
    {
        try {
            $loan = Loan::findOrFail($loanId);
            
            $loan->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->reason ?? 'Rejected via Approval Center',
            ]);
            
            activity()
                ->performedOn($loan)
                ->causedBy(auth()->user())
                ->log("Loan rejected via Approval Center: {$loan->loan_number}");
            
            return response()->json([
                'success' => true,
                'message' => 'Loan rejected successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting loan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve savings account
     */
    public function approveSavings(Request $request, $savingsId)
    {
        try {
            $savings = SavingsAccount::findOrFail($savingsId);
            
            $savings->update([
                'status' => 'active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            
            activity()
                ->performedOn($savings)
                ->causedBy(auth()->user())
                ->log("Savings account approved: {$savings->account_number}");
            
            return response()->json([
                'success' => true,
                'message' => 'Savings account approved!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject savings account
     */
    public function rejectSavings(Request $request, $savingsId)
    {
        try {
            $savings = SavingsAccount::findOrFail($savingsId);
            
            $savings->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
            ]);
            
            return response()->json(['success' => true, 'message' => 'Savings account rejected!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Approve KYC document
     */
    public function approveKyc(Request $request, $kycId)
    {
        try {
            $kyc = KycDocument::findOrFail($kycId);
            
            $kyc->update([
                'verification_status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'verification_notes' => $request->notes ?? 'Approved',
            ]);
            
            activity()
                ->performedOn($kyc)
                ->causedBy(auth()->user())
                ->log("KYC document verified: {$kyc->document_type}");
            
            return response()->json(['success' => true, 'message' => 'KYC document verified!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject KYC document
     */
    public function rejectKyc(Request $request, $kycId)
    {
        try {
            $kyc = KycDocument::findOrFail($kycId);
            
            $kyc->update([
                'verification_status' => 'rejected',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'verification_notes' => $request->reason ?? 'Rejected',
            ]);
            
            return response()->json(['success' => true, 'message' => 'KYC document rejected!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Approve collateral
     */
    public function approveCollateral(Request $request, $collateralId)
    {
        try {
            $collateral = Collateral::findOrFail($collateralId);
            
            $collateral->update([
                'status' => 'active',
                'valuation_date' => now(),
                'valued_by' => auth()->id(),
            ]);
            
            activity()
                ->performedOn($collateral)
                ->causedBy(auth()->user())
                ->log("Collateral verified: {$collateral->type}");
            
            return response()->json(['success' => true, 'message' => 'Collateral verified!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject collateral
     */
    public function rejectCollateral(Request $request, $collateralId)
    {
        try {
            $collateral = Collateral::findOrFail($collateralId);
            
            $collateral->update([
                'status' => 'rejected',
                'valued_by' => auth()->id(),
                'valuation_date' => now(),
            ]);
            
            return response()->json(['success' => true, 'message' => 'Collateral rejected!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Approve client
     */
    public function approveClient(Request $request, $clientId)
    {
        try {
            $client = Client::findOrFail($clientId);
            
            $client->update([
                'status' => 'active',
                'kyc_status' => 'verified',
            ]);
            
            activity()
                ->performedOn($client)
                ->causedBy(auth()->user())
                ->log("Client approved: {$client->full_name}");
            
            return response()->json(['success' => true, 'message' => 'Client approved!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Reject client
     */
    public function rejectClient(Request $request, $clientId)
    {
        try {
            $client = Client::findOrFail($clientId);
            
            $client->update([
                'status' => 'rejected',
                'kyc_status' => 'rejected',
            ]);
            
            return response()->json(['success' => true, 'message' => 'Client rejected!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

