<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanApplication extends Model
{
    protected $fillable = [
        'application_number',
        'client_id',
        'branch_id',
        'loan_officer_id',
        'loan_type',
        'requested_amount',
        'approved_amount',
        'requested_term_months',
        'approved_term_months',
        'requested_interest_rate',
        'approved_interest_rate',
        'payment_frequency',
        'loan_purpose',
        'business_description',
        'monthly_income',
        'monthly_expenses',
        'collateral_description',
        'collateral_value',
        'status',
        'kyc_status',
        'credit_check_status',
        'credit_score',
        'rejection_reason',
        'notes',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'risk_level',
        'risk_assessment_notes',
        'ltv_ratio'
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'requested_interest_rate' => 'decimal:2',
        'approved_interest_rate' => 'decimal:2',
        'monthly_income' => 'decimal:2',
        'monthly_expenses' => 'decimal:2',
        'collateral_value' => 'decimal:2',
        'credit_score' => 'decimal:2',
        'ltv_ratio' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
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

    public function loanOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loan_officer_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function loan(): HasOne
    {
        return $this->hasOne(Loan::class);
    }

    // Methods
    public function getNetIncome(): float
    {
        return ($this->monthly_income ?? 0) - ($this->monthly_expenses ?? 0);
    }

    public function calculateLTVRatio(): float
    {
        if (!$this->collateral_value || $this->collateral_value <= 0) {
            return 0;
        }
        
        $loanAmount = $this->approved_amount ?? $this->requested_amount;
        return ($loanAmount / $this->collateral_value) * 100;
    }

    public function getDebtToIncomeRatio(): float
    {
        $netIncome = $this->getNetIncome();
        if ($netIncome <= 0) {
            return 0;
        }
        
        $monthlyPayment = $this->calculateMonthlyPayment();
        return ($monthlyPayment / $netIncome) * 100;
    }

    public function calculateMonthlyPayment(): float
    {
        $amount = $this->approved_amount ?? $this->requested_amount;
        $rate = ($this->approved_interest_rate ?? $this->requested_interest_rate) / 100 / 12;
        $months = $this->approved_term_months ?? $this->requested_term_months;
        
        if ($amount <= 0 || $rate <= 0 || $months <= 0) {
            return 0;
        }
        
        $numerator = $amount * $rate * pow(1 + $rate, $months);
        $denominator = pow(1 + $rate, $months) - 1;
        
        return $denominator > 0 ? $numerator / $denominator : 0;
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['draft', 'submitted', 'under_review']);
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve($approvedBy): bool
    {
        $this->status = 'approved';
        $this->approved_by = $approvedBy;
        $this->approved_at = now();
        return $this->save();
    }

    public function reject($rejectedBy, $reason = null): bool
    {
        $this->status = 'rejected';
        $this->rejected_by = $rejectedBy;
        $this->rejected_at = now();
        if ($reason) {
            $this->rejection_reason = $reason;
        }
        return $this->save();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted', 'under_review']);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByLoanOfficer($query, $officerId)
    {
        return $query->where('loan_officer_id', $officerId);
    }
}
