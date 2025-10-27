<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanRepayment;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class BorrowerReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:borrower');
    }

    /**
     * Display borrower's financial report
     */
    public function financial(Request $request)
    {
        $user = Auth::user();
        $client = $user->client;

        if (!$client) {
            return redirect()->route('borrower.profile')->with('error', 'Please complete your profile first.');
        }

        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        // Get borrower's loans data
        $loans = $client->loans()->with('transactions')->get();
        $activeLoans = $loans->whereIn('status', ['active', 'disbursed']);
        
        // Calculate loan statistics
        $loanStats = [
            'total_borrowed' => $loans->whereIn('status', ['active', 'disbursed', 'closed', 'paid_off'])->sum('amount'),
            'outstanding_balance' => $activeLoans->sum('outstanding_balance'),
            'total_paid' => $loans->sum('total_paid'),
            'active_loans_count' => $activeLoans->count(),
            'completed_loans_count' => $loans->whereIn('status', ['closed', 'paid_off'])->count(),
        ];

        // Get payment history for the period
        $payments = LoanRepayment::whereHas('loan', function($q) use ($client) {
                $q->where('client_id', $client->id);
            })
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->with('loan')
            ->get();

        $paymentStats = [
            'total_payments' => $payments->sum('amount'),
            'principal_paid' => $payments->sum('principal_amount'),
            'interest_paid' => $payments->sum('interest_amount'),
            'penalty_paid' => $payments->sum('penalty_amount'),
            'payment_count' => $payments->count(),
        ];

        // Get savings data
        $savingsAccounts = $client->savingsAccounts;
        $savingsStats = [
            'total_savings' => $savingsAccounts->sum('balance'),
            'active_accounts' => $savingsAccounts->where('status', 'active')->count(),
            'total_deposits' => $client->transactions()
                ->where('type', 'deposit')
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->sum('amount'),
            'total_withdrawals' => $client->transactions()
                ->where('type', 'withdrawal')
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->sum('amount'),
        ];

        // Monthly trends (last 12 months)
        $trends = $this->getMonthlyTrends($client);

        // Upcoming payments
        $upcomingPayments = $this->getUpcomingPayments($client);

        // Credit score history
        $creditScore = $client->credit_score ?? 0;
        $creditStatus = $this->getCreditStatus($creditScore);

        if ($request->export === 'pdf') {
            $pdf = PDF::loadView('borrower.reports.financial-pdf', compact(
                'loanStats', 'paymentStats', 'savingsStats', 'trends', 'fromDate', 'toDate', 'client'
            ));
            return $pdf->download('my-financial-report-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('borrower.reports.financial', compact(
            'loanStats',
            'paymentStats', 
            'savingsStats',
            'trends',
            'upcomingPayments',
            'creditScore',
            'creditStatus',
            'fromDate',
            'toDate'
        ));
    }

    /**
     * Get monthly payment trends for last 12 months
     */
    private function getMonthlyTrends($client)
    {
        $trends = [];
        $startDate = now()->subMonths(12)->startOfMonth();

        for ($i = 0; $i < 12; $i++) {
            $monthStart = $startDate->copy()->addMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $payments = LoanRepayment::whereHas('loan', function($q) use ($client) {
                    $q->where('client_id', $client->id);
                })
                ->whereBetween('payment_date', [$monthStart, $monthEnd])
                ->get();

            $trends[] = [
                'month' => $monthStart->format('M Y'),
                'payments' => $payments->sum('amount'),
                'principal' => $payments->sum('principal_amount'),
                'interest' => $payments->sum('interest_amount'),
            ];
        }

        return $trends;
    }

    /**
     * Get upcoming payments in next 30 days
     */
    private function getUpcomingPayments($client)
    {
        return $client->loans()
            ->whereIn('status', ['active', 'disbursed'])
            ->where('next_due_date', '>=', now())
            ->where('next_due_date', '<=', now()->addDays(30))
            ->orderBy('next_due_date')
            ->get();
    }

    /**
     * Get credit status based on score
     */
    private function getCreditStatus($score)
    {
        if ($score >= 750) {
            return ['label' => 'Excellent', 'class' => 'success'];
        } elseif ($score >= 650) {
            return ['label' => 'Good', 'class' => 'info'];
        } elseif ($score >= 550) {
            return ['label' => 'Fair', 'class' => 'warning'];
        } else {
            return ['label' => 'Needs Improvement', 'class' => 'danger'];
        }
    }
}

