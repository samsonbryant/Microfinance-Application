<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Expense;
use App\Models\ChartOfAccount;
use App\Models\Bank;

class ExpenseFormLive extends Component
{
    public $transaction_date;
    public $account_id;
    public $description;
    public $amount;
    public $payment_method = 'cash';
    public $bank_id;
    public $reference_number;
    public $payee_name;
    
    public $accounts;
    public $banks;
    public $selectedAccount;

    protected $rules = [
        'transaction_date' => 'required|date',
        'account_id' => 'required|exists:chart_of_accounts,id',
        'description' => 'required|string|min:3',
        'amount' => 'required|numeric|min:0.01',
        'payment_method' => 'required|in:cash,cheque,bank_transfer,mobile_money',
        'bank_id' => 'required_if:payment_method,cheque,bank_transfer|nullable|exists:banks,id',
        'reference_number' => 'nullable|string|max:255',
        'payee_name' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $this->transaction_date = now()->toDateString();
        $this->accounts = ChartOfAccount::where('type', 'expense')->where('is_active', true)->get();
        $this->banks = Bank::where('is_active', true)->get();
    }

    public function updatedAccountId($value)
    {
        $this->selectedAccount = ChartOfAccount::find($value);
    }

    public function updatedPaymentMethod($value)
    {
        if ($value === 'cash') {
            $this->bank_id = null;
            $this->reference_number = null;
        }
    }

    public function save()
    {
        $this->validate();

        $expense = Expense::create([
            'expense_number' => Expense::generateExpenseNumber(),
            'transaction_date' => $this->transaction_date,
            'account_id' => $this->account_id,
            'description' => $this->description,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'bank_id' => $this->bank_id,
            'reference_number' => $this->reference_number,
            'payee_name' => $this->payee_name,
            'branch_id' => auth()->user()->branch_id,
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        activity()
            ->performedOn($expense)
            ->causedBy(auth()->user())
            ->log('Created expense via Livewire: ' . $expense->expense_number);

        session()->flash('success', 'Expense created successfully. Awaiting approval.');

        $this->reset(['account_id', 'description', 'amount', 'payment_method', 'bank_id', 'reference_number', 'payee_name']);
        $this->transaction_date = now()->toDateString();

        $this->dispatch('expenseCreated', $expense->id);
    }

    public function render()
    {
        return view('livewire.expense-form-live');
    }
}

