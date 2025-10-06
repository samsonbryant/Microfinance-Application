<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Get dashboard statistics
        $stats = [
            'total_users' => User::count(),
            'total_branches' => Branch::count(),
            'total_clients' => Client::count(),
            'total_loans' => Loan::count(),
            'total_savings_accounts' => SavingsAccount::count(),
            'total_transactions' => Transaction::count(),
        ];

        // Get loan statistics
        $loanStats = [
            'active_loans' => Loan::where('status', 'active')->count(),
            'overdue_loans' => Loan::where('status', 'overdue')->count(),
            'total_loan_amount' => Loan::whereIn('status', ['active', 'overdue', 'disbursed'])->sum('amount'),
            'total_outstanding' => Loan::whereIn('status', ['active', 'overdue', 'disbursed'])->sum('outstanding_balance'),
        ];

        // Get savings statistics
        $savingsStats = [
            'active_accounts' => SavingsAccount::where('status', 'active')->count(),
            'total_balance' => SavingsAccount::where('status', 'active')->sum('balance'),
        ];

        // Get recent transactions
        $recentTransactions = Transaction::with(['client', 'loan'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent clients
        $recentClients = Client::with('branch')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get monthly loan disbursements for chart (SQLite compatible)
        $monthlyDisbursements = Loan::select(
                DB::raw('strftime("%m", disbursement_date) as month'),
                DB::raw('strftime("%Y", disbursement_date) as year'),
                DB::raw('SUM(amount) as total_amount')
            )
            ->where('disbursement_date', '>=', now()->subMonths(6))
            ->whereNotNull('disbursement_date')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return view('home', compact(
            'stats',
            'loanStats',
            'savingsStats',
            'recentTransactions',
            'recentClients',
            'monthlyDisbursements'
        ));
    }
}
