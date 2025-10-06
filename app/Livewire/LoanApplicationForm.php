<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\Loan;
use App\Models\Collateral;
use App\Services\LoanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanApplicationForm extends Component
{
    public $client_id;
    public $amount;
    public $interest_rate;
    public $term_months;
    public $loan_type;
    public $purpose;
    public $collateral_type;
    public $collateral_value;
    public $collateral_description;
    public $clients = [];
    public $showCollateralForm = false;
    public $isSubmitting = false;

    protected $rules = [
        'client_id' => 'required|exists:clients,id',
        'amount' => 'required|numeric|min:1000|max:1000000',
        'interest_rate' => 'required|numeric|min:1|max:50',
        'term_months' => 'required|integer|min:1|max:60',
        'loan_type' => 'required|in:personal,business,agricultural,emergency',
        'purpose' => 'required|string|max:500',
        'collateral_type' => 'required_if:loan_type,personal|string|max:100',
        'collateral_value' => 'required_if:loan_type,personal|numeric|min:0',
        'collateral_description' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $this->loadClients();
        $this->interest_rate = 12; // Default interest rate
        $this->term_months = 12; // Default term
    }

    public function loadClients()
    {
        $this->clients = Client::where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function updatedLoanType()
    {
        $this->showCollateralForm = in_array($this->loan_type, ['personal', 'business']);
    }

    public function calculateLTV()
    {
        if ($this->amount && $this->collateral_value) {
            $ltv = ($this->amount / $this->collateral_value) * 100;
            return round($ltv, 2);
        }
        return 0;
    }

    public function calculateMonthlyPayment()
    {
        if ($this->amount && $this->interest_rate && $this->term_months) {
            $monthlyRate = $this->interest_rate / 100 / 12;
            $payment = $this->amount * ($monthlyRate * pow(1 + $monthlyRate, $this->term_months)) / (pow(1 + $monthlyRate, $this->term_months) - 1);
            return round($payment, 2);
        }
        return 0;
    }

    public function submit()
    {
        $this->validate();
        
        $this->isSubmitting = true;

        try {
            DB::beginTransaction();

            // Create loan application
            $loan = Loan::create([
                'client_id' => $this->client_id,
                'amount' => $this->amount,
                'interest_rate' => $this->interest_rate,
                'term_months' => $this->term_months,
                'loan_type' => $this->loan_type,
                'purpose' => $this->purpose,
                'status' => 'pending',
                'loan_officer_id' => Auth::id(),
                'outstanding_balance' => $this->amount,
                'next_payment_amount' => $this->calculateMonthlyPayment(),
                'next_due_date' => now()->addMonth(),
            ]);

            // Create collateral if provided
            if ($this->showCollateralForm && $this->collateral_type && $this->collateral_value) {
                Collateral::create([
                    'loan_id' => $loan->id,
                    'type' => $this->collateral_type,
                    'value' => $this->collateral_value,
                    'description' => $this->collateral_description,
                    'status' => 'active',
                ]);
            }

            DB::commit();

            session()->flash('success', 'Loan application submitted successfully!');
            $this->resetForm();
            
            // Dispatch event to refresh dashboard
            $this->dispatch('loanApplicationSubmitted');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to submit loan application: ' . $e->getMessage());
        } finally {
            $this->isSubmitting = false;
        }
    }

    public function resetForm()
    {
        $this->reset([
            'client_id',
            'amount',
            'interest_rate',
            'term_months',
            'loan_type',
            'purpose',
            'collateral_type',
            'collateral_value',
            'collateral_description',
            'showCollateralForm'
        ]);
    }

    public function render()
    {
        return view('livewire.loan-application-form');
    }
}