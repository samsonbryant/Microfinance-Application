<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    protected $fillable = [
        'loan_number',
        'client_id',
        'branch_id',
        'collateral_id',
        'loan_type',
        'amount',
        'principal_amount',
        'currency',
        'interest_rate',
        'term_months',
        'loan_term',
        'payment_frequency',
        'disbursement_date',
        'release_date',
        'due_date',
        'duration_period',
        'interest_method',
        'interest_cycle',
        'repayment_type',
        'repayment_cycle',
        'repayment_days',
        'late_penalty_enabled',
        'late_penalty_amount',
        'late_penalty_type',
        'funding_account_id',
        'loans_receivable_account_id',
        'interest_income_account_id',
        'fees_income_account_id',
        'penalty_income_account_id',
        'overpayment_account_id',
        'files',
        'credit_risk_score',
        'status',
        'outstanding_balance',
        'total_paid',
        'penalty_rate',
        'notes',
        'created_by',
        'loan_purpose',
        'application_date',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reviewed_by',
        'reviewed_at',
        'monthly_payment',
        'total_interest',
        'total_amount',
        'repayment_schedule',
        'next_due_date',
        'next_payment_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'penalty_rate' => 'decimal:2',
        'late_penalty_amount' => 'decimal:2',
        'credit_risk_score' => 'decimal:2',
        'late_penalty_enabled' => 'boolean',
        'disbursement_date' => 'date',
        'release_date' => 'date',
        'due_date' => 'date',
        'application_date' => 'date',
        'next_due_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'repayment_days' => 'array',
        'files' => 'array',
        'repayment_schedule' => 'array',
        'monthly_payment' => 'decimal:2',
        'total_interest' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'next_payment_amount' => 'decimal:2',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function collateral(): BelongsTo
    {
        return $this->belongsTo(Collateral::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(LoanFee::class);
    }

    public function fundingAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'funding_account_id');
    }

    public function loansReceivableAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'loans_receivable_account_id');
    }

    public function interestIncomeAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'interest_income_account_id');
    }

    // Methods
    /**
     * Calculate simple interest: Principal × Interest Rate (%)
     * This is a straightforward percentage of the principal amount
     */
    public function calculateTotalInterest(): float
    {
        if ($this->amount <= 0 || $this->interest_rate <= 0) {
            return 0;
        }

        // Simple interest: Principal × (Rate / 100)
        return $this->amount * ($this->interest_rate / 100);
    }

    /**
     * Calculate total amount: Principal + Interest
     */
    public function calculateTotalAmount(): float
    {
        return $this->amount + $this->calculateTotalInterest();
    }

    /**
     * Calculate monthly payment: Total Amount ÷ Term
     */
    public function calculateMonthlyPayment(): float
    {
        if ($this->term_months <= 0) {
            return 0;
        }

        return $this->calculateTotalAmount() / $this->term_months;
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || 
               ($this->due_date && $this->due_date < now() && $this->outstanding_balance > 0);
    }

    public function getNextPaymentDue()
    {
        // Calculate next payment date based on payment frequency
        if (!$this->disbursement_date) {
            return null;
        }

        $lastPayment = $this->transactions()
            ->where('type', 'loan_repayment')
            ->latest('created_at')
            ->first();

        if ($lastPayment) {
            $baseDate = $lastPayment->created_at;
        } else {
            $baseDate = $this->disbursement_date;
        }

        switch ($this->payment_frequency) {
            case 'daily':
                return $baseDate->copy()->addDay();
            case 'weekly':
                return $baseDate->copy()->addWeek();
            case 'monthly':
                return $baseDate->copy()->addMonth();
            case 'quarterly':
                return $baseDate->copy()->addMonths(3);
            default:
                return $this->due_date;
        }
    }

    public function getDaysOverdue()
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        $nextPaymentDue = $this->getNextPaymentDue();
        if (!$nextPaymentDue) {
            return 0;
        }

        return now()->diffInDays($nextPaymentDue, false);
    }
}
