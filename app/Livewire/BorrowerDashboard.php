<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Loan;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class BorrowerDashboard extends Component
{
    public $stats;
    public $loans;
    public $savingsAccounts;
    public $recentTransactions;
    public $nextPayment;
    public $refreshInterval = 30000; // 30 seconds

    protected $listeners = [
        'loan.updated' => 'refreshData',
        'transaction.created' => 'refreshData',
        'payment.processed' => 'refreshData',
    ];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $user = Auth::user();
        $client = $user->client;

        if (!$client) {
            $this->stats = $this->getEmptyStats();
            return;
        }

        // Get loans with real-time data
        $this->loans = $client->loans()
            ->with(['collaterals', 'repayments'])
            ->latest()
            ->get();

        // Get savings accounts
        $this->savingsAccounts = $client->savingsAccounts()
            ->where('status', 'active')
            ->get();

        // Get recent transactions
        $this->recentTransactions = $client->transactions()
            ->with(['loan', 'savingsAccount'])
            ->latest()
            ->limit(10)
            ->get();

        // Calculate statistics in real-time
        $this->stats = [
            'total_loans' => $this->loans->count(),
            'active_loans' => $this->loans->whereIn('status', ['active', 'disbursed'])->count(),
            'pending_applications' => $this->loans->where('status', 'pending')->count(),
            'outstanding_balance' => $this->loans->whereIn('status', ['active', 'disbursed'])->sum('outstanding_balance'),
            'total_borrowed' => $this->loans->whereIn('status', ['active', 'disbursed', 'closed', 'paid_off'])->sum('amount'),
            'total_paid' => $this->loans->sum('total_paid'),
            'savings_balance' => $this->savingsAccounts->sum('balance'),
            'savings_accounts' => $this->savingsAccounts->count(),
            'next_payment_amount' => 0,
            'next_payment_date' => null,
            'credit_score' => $client->credit_score ?? 0,
        ];

        // Get next payment
        $this->nextPayment = $this->loans
            ->whereIn('status', ['active', 'disbursed'])
            ->where('next_due_date', '>=', now())
            ->sortBy('next_due_date')
            ->first();

        if ($this->nextPayment) {
            $this->stats['next_payment_amount'] = $this->nextPayment->next_payment_amount ?? 0;
            $this->stats['next_payment_date'] = $this->nextPayment->next_due_date;
        }
    }

    public function refreshData()
    {
        $this->loadData();
        $this->dispatch('dataRefreshed');
    }

    private function getEmptyStats()
    {
        return [
            'total_loans' => 0,
            'active_loans' => 0,
            'pending_applications' => 0,
            'outstanding_balance' => 0,
            'total_borrowed' => 0,
            'total_paid' => 0,
            'savings_balance' => 0,
            'savings_accounts' => 0,
            'next_payment_amount' => 0,
            'next_payment_date' => null,
            'credit_score' => 0,
        ];
    }

    public function render()
    {
        return view('livewire.borrower-dashboard');
    }
}

