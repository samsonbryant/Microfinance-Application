<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\RevenueEntry;
use App\Models\ChartOfAccount;
use App\Models\Bank;

class RevenueFormLive extends Component
{
    public $transaction_date;
    public $account_id;
    public $revenue_type = 'other';
    public $description;
    public $amount;
    public $bank_id;
    public $reference_number;
    
    public $accounts;
    public $banks;

    protected $rules = [
        'transaction_date' => 'required|date',
        'account_id' => 'required|exists:chart_of_accounts,id',
        'revenue_type' => 'required|in:interest_received,default_charges,processing_fee,system_charge,other',
        'description' => 'required|string|min:3',
        'amount' => 'required|numeric|min:0.01',
        'bank_id' => 'nullable|exists:banks,id',
        'reference_number' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $this->transaction_date = now()->toDateString();
        $this->accounts = ChartOfAccount::where('type', 'revenue')->where('is_active', true)->get();
        $this->banks = Bank::where('is_active', true)->get();
    }

    public function updatedRevenueType($value)
    {
        // Auto-suggest account based on revenue type
        $accountCodes = [
            'interest_received' => '4000',
            'default_charges' => '4100',
            'processing_fee' => '4200',
            'system_charge' => '4300',
            'other' => '4400',
        ];

        $account = ChartOfAccount::where('code', $accountCodes[$value] ?? '4400')->first();
        if ($account) {
            $this->account_id = $account->id;
        }
    }

    public function save()
    {
        $this->validate();

        $revenue = RevenueEntry::create([
            'revenue_number' => RevenueEntry::generateRevenueNumber(),
            'transaction_date' => $this->transaction_date,
            'account_id' => $this->account_id,
            'revenue_type' => $this->revenue_type,
            'description' => $this->description,
            'amount' => $this->amount,
            'bank_id' => $this->bank_id,
            'reference_number' => $this->reference_number,
            'branch_id' => auth()->user()->branch_id,
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        activity()
            ->performedOn($revenue)
            ->causedBy(auth()->user())
            ->log('Created revenue entry via Livewire: ' . $revenue->revenue_number);

        session()->flash('success', 'Revenue entry created successfully. Awaiting approval.');

        $this->reset(['account_id', 'revenue_type', 'description', 'amount', 'bank_id', 'reference_number']);
        $this->transaction_date = now()->toDateString();
        $this->revenue_type = 'other';

        $this->dispatch('revenueCreated', $revenue->id);
    }

    public function render()
    {
        return view('livewire.revenue-form-live');
    }
}

