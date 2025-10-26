<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transfer;
use App\Models\ChartOfAccount;
use App\Models\Bank;

class TransferFormLive extends Component
{
    public $transaction_date;
    public $from_account_id;
    public $to_account_id;
    public $from_bank_id;
    public $to_bank_id;
    public $amount;
    public $type = 'transfer';
    public $reference_number;
    public $description;
    
    public $accounts;
    public $banks;

    protected $rules = [
        'transaction_date' => 'required|date',
        'from_account_id' => 'required|exists:chart_of_accounts,id',
        'to_account_id' => 'required|exists:chart_of_accounts,id|different:from_account_id',
        'from_bank_id' => 'nullable|exists:banks,id',
        'to_bank_id' => 'nullable|exists:banks,id',
        'amount' => 'required|numeric|min:0.01',
        'type' => 'required|in:deposit,withdrawal,disbursement,expense,transfer',
        'reference_number' => 'nullable|string|max:255',
        'description' => 'required|string|min:3',
    ];

    public function mount()
    {
        $this->transaction_date = now()->toDateString();
        $this->accounts = ChartOfAccount::where('is_active', true)->get();
        $this->banks = Bank::where('is_active', true)->get();
    }

    public function save()
    {
        $this->validate();

        $transfer = Transfer::create([
            'transfer_number' => Transfer::generateTransferNumber(),
            'transaction_date' => $this->transaction_date,
            'from_account_id' => $this->from_account_id,
            'to_account_id' => $this->to_account_id,
            'from_bank_id' => $this->from_bank_id,
            'to_bank_id' => $this->to_bank_id,
            'amount' => $this->amount,
            'type' => $this->type,
            'reference_number' => $this->reference_number,
            'description' => $this->description,
            'branch_id' => auth()->user()->branch_id,
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        activity()
            ->performedOn($transfer)
            ->causedBy(auth()->user())
            ->log('Created transfer via Livewire: ' . $transfer->transfer_number);

        session()->flash('success', 'Transfer created successfully. Awaiting approval.');

        $this->reset(['from_account_id', 'to_account_id', 'from_bank_id', 'to_bank_id', 'amount', 'type', 'reference_number', 'description']);
        $this->transaction_date = now()->toDateString();
        $this->type = 'transfer';

        $this->dispatch('transferCreated', $transfer->id);
    }

    public function render()
    {
        return view('livewire.transfer-form-live');
    }
}

