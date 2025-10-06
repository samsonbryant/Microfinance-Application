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
        'interest_rate',
        'term_months',
        'payment_frequency',
        'disbursement_date',
        'due_date',
        'status',
        'outstanding_balance',
        'total_paid',
        'penalty_rate',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'penalty_rate' => 'decimal:2',
        'disbursement_date' => 'date',
        'due_date' => 'date',
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
}
