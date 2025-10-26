<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\AccountingService;
use App\Models\Expense;
use App\Models\Transfer;
use App\Models\RevenueEntry;
use App\Models\JournalEntry;

class AccountingDashboard extends Component
{
    public $fromDate;
    public $toDate;
    public $profitLoss;
    public $cashPosition;
    public $revenueBreakdown;
    public $pendingApprovals;

    protected $listeners = [
        'expense.posted' => 'refreshMetrics',
        'revenue.posted' => 'refreshMetrics',
        'transfer.processed' => 'refreshMetrics',
        'journal-entry.posted' => 'refreshMetrics',
    ];

    public function mount()
    {
        $this->fromDate = now()->startOfMonth()->toDateString();
        $this->toDate = now()->toDateString();
        $this->loadMetrics();
    }

    public function updatedFromDate()
    {
        $this->loadMetrics();
    }

    public function updatedToDate()
    {
        $this->loadMetrics();
    }

    public function loadMetrics()
    {
        $accountingService = app(AccountingService::class);

        $this->profitLoss = $accountingService->getProfitAndLoss($this->fromDate, $this->toDate);
        $this->cashPosition = $accountingService->getCashPosition($this->toDate);
        $this->revenueBreakdown = $accountingService->getRevenueBreakdown($this->fromDate, $this->toDate);
        
        $this->pendingApprovals = [
            'expenses' => Expense::where('status', 'pending')->count(),
            'revenues' => RevenueEntry::where('status', 'pending')->count(),
            'transfers' => Transfer::where('status', 'pending')->count(),
            'journals' => JournalEntry::where('status', 'pending')->count(),
        ];
    }

    public function refreshMetrics()
    {
        $this->loadMetrics();
        $this->dispatch('metricsUpdated');
    }

    public function render()
    {
        return view('livewire.accounting-dashboard');
    }
}

