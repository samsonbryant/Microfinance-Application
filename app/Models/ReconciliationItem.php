<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ReconciliationItem extends Model
{
    protected $fillable = [
        'reconciliation_id',
        'reference_type',
        'reference_id',
        'transaction_date',
        'description',
        'amount',
        'status',
        'external_reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    // Relationships
    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(Reconciliation::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    // Methods
    public function isMatched()
    {
        return $this->status === 'matched';
    }

    public function isUnmatched()
    {
        return $this->status === 'unmatched';
    }

    public function isDisputed()
    {
        return $this->status === 'disputed';
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'matched' => 'success',
            'unmatched' => 'warning',
            'disputed' => 'danger',
            default => 'secondary',
        };
    }

    public function getFormattedAmount()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function match()
    {
        $this->update(['status' => 'matched']);
    }

    public function dispute($notes = null)
    {
        $this->update([
            'status' => 'disputed',
            'notes' => $notes
        ]);
    }

    public function unmatch()
    {
        $this->update(['status' => 'unmatched']);
    }

    // Static methods
    public static function createFromGeneralLedgerEntry($reconciliationId, $entry)
    {
        return static::create([
            'reconciliation_id' => $reconciliationId,
            'reference_type' => 'general_ledger_entry',
            'reference_id' => $entry->id,
            'transaction_date' => $entry->transaction_date,
            'description' => $entry->description,
            'amount' => $entry->debit > 0 ? $entry->debit : $entry->credit,
            'status' => 'unmatched',
            'external_reference' => $entry->reference_number,
        ]);
    }

    public static function createFromBankStatement($reconciliationId, $statementData)
    {
        return static::create([
            'reconciliation_id' => $reconciliationId,
            'reference_type' => 'bank_statement',
            'reference_id' => null,
            'transaction_date' => $statementData['date'],
            'description' => $statementData['description'],
            'amount' => $statementData['amount'],
            'status' => 'unmatched',
            'external_reference' => $statementData['reference'],
        ]);
    }
}
