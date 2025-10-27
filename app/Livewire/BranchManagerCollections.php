<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Loan;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class BranchManagerCollections extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $selectedTab = 'due-today';
    public $selectedLoan = null;
    public $paymentAmount = 0;
    public $paymentMethod = 'cash';
    public $paymentDate;
    public $referenceNumber = '';
    public $notes = '';
    public $showPaymentModal = false;

    protected $listeners = ['refreshCollections' => '$refresh'];

    public function mount()
    {
        $this->paymentDate = now()->format('Y-m-d');
    }

    public function selectTab($tab)
    {
        $this->selectedTab = $tab;
        $this->resetPage();
    }

    public function openPaymentModal($loanId)
    {
        $this->selectedLoan = Loan::with('client')->findOrFail($loanId);
        $this->paymentAmount = $this->selectedLoan->next_payment_amount ?? 0;
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->selectedLoan = null;
        $this->reset(['paymentAmount', 'paymentMethod', 'referenceNumber', 'notes']);
        $this->paymentDate = now()->format('Y-m-d');
    }

    public function processPayment()
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentMethod' => 'required|in:cash,bank_transfer,mobile_money,cheque',
            'paymentDate' => 'required|date',
            'referenceNumber' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $loan = $this->selectedLoan;

            // Ensure loan can accept payments
            if (!in_array($loan->status, ['active', 'overdue'])) {
                session()->flash('error', 'This loan cannot accept repayments.');
                return;
            }

            // Create transaction
            $transaction = Transaction::create([
                'transaction_number' => 'REP' . now()->format('Ymd') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                'client_id' => $loan->client_id,
                'loan_id' => $loan->id,
                'type' => 'loan_repayment',
                'amount' => $this->paymentAmount,
                'description' => 'Loan repayment for ' . $loan->loan_number . ($this->notes ? ' - ' . $this->notes : ''),
                'reference_number' => $this->referenceNumber ?: null,
                'status' => 'completed',
                'branch_id' => $loan->branch_id,
                'created_by' => auth()->id(),
                'processed_at' => $this->paymentDate,
            ]);

            // Calculate new balance
            $newBalance = $loan->outstanding_balance - $this->paymentAmount;
            
            // Update loan
            $loan->update([
                'outstanding_balance' => max($newBalance, 0),
                'total_paid' => ($loan->total_paid ?? 0) + $this->paymentAmount,
                'status' => $newBalance <= 0 ? 'completed' : ($loan->status === 'overdue' && $newBalance < $loan->outstanding_balance ? 'active' : $loan->status),
                'last_payment_date' => $this->paymentDate,
            ]);

            // Update next due date if necessary
            if ($newBalance <= 0) {
                $loan->update(['next_due_date' => null]);
            } elseif ($loan->next_due_date && $loan->next_due_date < now()) {
                // Calculate next payment date based on loan term
                $loan->update([
                    'next_due_date' => now()->addMonth()
                ]);
            }

            // Log activity
            activity()
                ->performedOn($loan)
                ->causedBy(auth()->user())
                ->log("Repayment of $" . number_format($this->paymentAmount, 2) . " recorded for loan {$loan->loan_number}");

            DB::commit();

            session()->flash('success', 'Payment processed successfully! Transaction #' . $transaction->transaction_number);
            
            $this->closePaymentModal();
            $this->dispatch('refreshCollections');
            $this->dispatch('payment-processed');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Payment processing error: ' . $e->getMessage());
            session()->flash('error', 'Error processing payment: ' . $e->getMessage());
        }
    }

    public function getDueToday()
    {
        return Loan::with(['client'])
            ->whereIn('status', ['active', 'overdue'])
            ->whereDate('next_due_date', today())
            ->where('branch_id', auth()->user()->branch_id)
            ->orderBy('next_due_date')
            ->get();
    }

    public function getOverdue()
    {
        return Loan::with(['client'])
            ->whereIn('status', ['active', 'overdue'])
            ->where('next_due_date', '<', now())
            ->where('branch_id', auth()->user()->branch_id)
            ->orderBy('next_due_date')
            ->get();
    }

    public function getUpcoming()
    {
        return Loan::with(['client'])
            ->where('status', 'active')
            ->whereBetween('next_due_date', [
                now()->addDay()->startOfDay(),
                now()->addDays(30)->endOfDay()
            ])
            ->where('branch_id', auth()->user()->branch_id)
            ->orderBy('next_due_date')
            ->get();
    }

    public function getAllActive()
    {
        return Loan::with(['client'])
            ->whereIn('status', ['active', 'overdue'])
            ->where('branch_id', auth()->user()->branch_id)
            ->orderBy('next_due_date')
            ->paginate(15);
    }

    public function getStats()
    {
        $branchId = auth()->user()->branch_id;

        $dueToday = Loan::whereIn('status', ['active', 'overdue'])
            ->whereDate('next_due_date', today())
            ->where('branch_id', $branchId);

        $overdue = Loan::whereIn('status', ['active', 'overdue'])
            ->where('next_due_date', '<', now())
            ->where('branch_id', $branchId);

        $active = Loan::whereIn('status', ['active', 'overdue'])
            ->where('branch_id', $branchId);

        $upcoming = Loan::where('status', 'active')
            ->whereBetween('next_due_date', [
                now()->addDay()->startOfDay(),
                now()->addDays(30)->endOfDay()
            ])
            ->where('branch_id', $branchId);

        return [
            'due_today_count' => $dueToday->count(),
            'due_today_amount' => $dueToday->sum('next_payment_amount'),
            'overdue_count' => $overdue->count(),
            'overdue_amount' => $overdue->sum('outstanding_balance'),
            'active_count' => $active->count(),
            'active_amount' => $active->sum('outstanding_balance'),
            'upcoming_count' => $upcoming->count(),
            'upcoming_amount' => $upcoming->sum('next_payment_amount'),
        ];
    }

    public function render()
    {
        $stats = $this->getStats();

        $loans = match($this->selectedTab) {
            'due-today' => $this->getDueToday(),
            'overdue' => $this->getOverdue(),
            'upcoming' => $this->getUpcoming(),
            'all-active' => $this->getAllActive(),
            default => $this->getDueToday(),
        };

        return view('livewire.branch-manager-collections', [
            'loans' => $loans,
            'stats' => $stats,
        ]);
    }
}

