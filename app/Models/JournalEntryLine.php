<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'debit',
        'credit',
        'description',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    // Relationships
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    // Methods
    public function isDebit()
    {
        return $this->debit > 0;
    }

    public function isCredit()
    {
        return $this->credit > 0;
    }

    public function getAmount()
    {
        return $this->debit > 0 ? $this->debit : $this->credit;
    }

    public function getFormattedAmount()
    {
        $amount = $this->getAmount();
        return '$' . number_format($amount, 2);
    }

    public function getFormattedDebit()
    {
        return $this->debit > 0 ? '$' . number_format($this->debit, 2) : '';
    }

    public function getFormattedCredit()
    {
        return $this->credit > 0 ? '$' . number_format($this->credit, 2) : '';
    }

    // Validation
    public function isValid()
    {
        // Must have either debit or credit, but not both
        return ($this->debit > 0 && $this->credit == 0) || ($this->credit > 0 && $this->debit == 0);
    }
}
