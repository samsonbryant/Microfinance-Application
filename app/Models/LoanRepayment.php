<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRepayment extends Model
{
    protected $fillable = [
        'loan_id',
        'repayment_date',
        'actual_payment_date',
        'principal_amount',
        'interest_amount',
        'penalty_amount',
        'total_paid',
        'payment_method',
        'status',
        'notes',
        'processed_by'
    ];

    protected $casts = [
        'repayment_date' => 'date',
        'actual_payment_date' => 'date',
        'principal_amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    // Relationships
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
