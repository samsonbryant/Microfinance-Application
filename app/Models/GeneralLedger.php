<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneralLedger extends Model
{
    protected $fillable = [
        'account_id',
        'debit',
        'credit',
        'description',
        'reference_id',
        'reference_type',
        'transaction_date',
        'created_by',
        'branch_id',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    // Relationships
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Methods
    public function getBalance()
    {
        return $this->debit - $this->credit;
    }

    public function isDebit()
    {
        return $this->debit > 0;
    }

    public function isCredit()
    {
        return $this->credit > 0;
    }

    public function getFormattedAmount()
    {
        if ($this->isDebit()) {
            return number_format($this->debit, 2);
        } else {
            return number_format($this->credit, 2);
        }
    }
}
