<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Loan;
use App\Models\Client;
use App\Models\LoanProduct;
use App\Services\LoanCalculationService;
use App\Events\LoanApplicationSubmitted;
use Illuminate\Support\Facades\DB;

class BorrowerLoanApplication extends Component
{
    public $amount = 0;
    public $interest_rate = 12;
    public $term_months = 12;
    public $purpose = '';
    public $employment_status = '';
    public $monthly_income = 0;
    public $existing_loans = 'no';
    public $collateral_description = '';
    public $loan_products = [];
    public $selected_product = null;
    
    // Calculated fields
    public $calculated_interest = 0;
    public $calculated_total = 0;
    public $calculated_monthly = 0;
    
    // Validation state
    public $showPreview = false;
    public $client = null;

    protected $rules = [
        'amount' => 'required|numeric|min:100|max:1000000',
        'interest_rate' => 'required|numeric|min:0|max:100',
        'term_months' => 'required|integer|min:1|max:360',
        'purpose' => 'required|string|max:500',
        'employment_status' => 'required|in:employed,self_employed,unemployed,retired',
        'monthly_income' => 'required|numeric|min:0',
        'existing_loans' => 'required|in:yes,no',
        'collateral_description' => 'nullable|string|max:1000',
    ];

    public function mount()
    {
        // Get borrower's client record
        $this->client = Client::where('user_id', auth()->id())->first();
        
        if (!$this->client) {
            session()->flash('error', 'Client profile not found. Please contact support.');
            return redirect()->route('borrower.dashboard');
        }

        // Load loan products if available
        $this->loan_products = LoanProduct::where('is_active', true)->get() ?? [];
    }

    public function updated($propertyName)
    {
        // Validate property
        $this->validateOnly($propertyName);
        
        // Recalculate when amount, rate, or term changes
        if (in_array($propertyName, ['amount', 'interest_rate', 'term_months'])) {
            $this->calculateLoan();
        }
    }

    public function selectProduct($productId)
    {
        $product = LoanProduct::find($productId);
        if ($product) {
            $this->selected_product = $productId;
            $this->interest_rate = $product->interest_rate;
            $this->term_months = $product->max_term_months ?? 12;
            $this->calculateLoan();
        }
    }

    public function calculateLoan()
    {
        if ($this->amount > 0 && $this->interest_rate >= 0 && $this->term_months > 0) {
            $calculationService = app(LoanCalculationService::class);
            $result = $calculationService->calculateSimpleInterest($this->amount, $this->interest_rate);
            
            $this->calculated_interest = $result['interest_amount'];
            $this->calculated_total = $result['total_amount'];
            $this->calculated_monthly = $this->calculated_total / $this->term_months;
            
            $this->showPreview = true;
        } else {
            $this->showPreview = false;
        }
    }

    public function preview()
    {
        $this->validate();
        $this->calculateLoan();
        $this->showPreview = true;
    }

    public function submit()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            // Create loan application
            $loan = Loan::create([
                'loan_number' => 'LN' . now()->format('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'client_id' => $this->client->id,
                'branch_id' => $this->client->branch_id,
                'amount' => $this->amount,
                'principal_amount' => $this->amount,
                'interest_rate' => $this->interest_rate,
                'term_months' => $this->term_months,
                'loan_term' => $this->term_months,
                'purpose' => $this->purpose,
                'status' => 'pending',
                'created_by' => auth()->id(),
                'loan_product_id' => $this->selected_product,
                'employment_status' => $this->employment_status,
                'monthly_income' => $this->monthly_income,
                'existing_loans' => $this->existing_loans === 'yes',
                'collateral_description' => $this->collateral_description,
                'application_date' => now(),
            ]);

            // Calculate loan details (done by LoanCreationObserver)
            // The observer will automatically calculate interest and schedule

            // Log activity
            activity()
                ->performedOn($loan)
                ->causedBy(auth()->user())
                ->log("Borrower submitted loan application for $" . number_format($this->amount, 2));

            // Broadcast event for real-time updates
            broadcast(new LoanApplicationSubmitted($loan))->toOthers();

            // Notify loan officer
            $loanOfficers = \App\Models\User::role('loan_officer')
                ->where('branch_id', $this->client->branch_id)
                ->get();
            
            foreach ($loanOfficers as $officer) {
                $officer->notify(new \App\Notifications\LoanApplicationNotification($loan));
            }

            DB::commit();

            session()->flash('success', 'Loan application submitted successfully! Application #' . $loan->loan_number);
            
            $this->dispatch('application-submitted', loanId: $loan->id);
            $this->reset(['amount', 'purpose', 'employment_status', 'monthly_income', 'collateral_description']);
            $this->showPreview = false;

            return redirect()->route('borrower.loans.index');

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Borrower loan application error: ' . $e->getMessage());
            session()->flash('error', 'Error submitting application: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.borrower-loan-application');
    }
}

