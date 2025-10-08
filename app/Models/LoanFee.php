<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanFee extends Model
{
    protected $fillable = [
        'loan_id',
        'fee_name',
        'fee_type',
        'fee_amount',
        'charge_type',
        'is_recurring',
        'description',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }
}
