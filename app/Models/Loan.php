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
        'created_by'
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
        'repayment_days' => 'array',
        'files' => 'array',
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
    public function calculateMonthlyPayment(): float
    {
        if ($this->amount <= 0 || $this->interest_rate <= 0 || $this->term_months <= 0) {
            return 0;
        }

        $monthlyRate = $this->interest_rate / 100 / 12;
        $numerator = $this->amount * $monthlyRate * pow(1 + $monthlyRate, $this->term_months);
        $denominator = pow(1 + $monthlyRate, $this->term_months) - 1;
        
        return $denominator > 0 ? $numerator / $denominator : 0;
    }

    public function calculateTotalInterest(): float
    {
        $monthlyPayment = $this->calculateMonthlyPayment();
        return ($monthlyPayment * $this->term_months) - $this->amount;
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
