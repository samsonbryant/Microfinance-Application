<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $fillable = [
        'journal_number',
        'branch_id',
        'user_id',
        'transaction_date',
        'description',
        'reference_number',
        'total_debits',
        'total_credits',
        'status',
        'approved_by',
        'approved_at',
        'posted_at',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'total_debits' => 'decimal:2',
        'total_credits' => 'decimal:2',
        'transaction_date' => 'date',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    // Methods
    public function isBalanced()
    {
        return $this->total_debits == $this->total_credits;
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isPosted()
    {
        return $this->status === 'posted';
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['draft', 'pending']);
    }

    public function canBeApproved()
    {
        return $this->status === 'pending' && $this->isBalanced();
    }

    public function canBePosted()
    {
        return $this->status === 'approved' && $this->isBalanced();
    }

    public function approve($userId)
    {
        if (!$this->canBeApproved()) {
            throw new \Exception('Journal entry cannot be approved');
        }

        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function reject($userId, $reason)
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function post()
    {
        if (!$this->canBePosted()) {
            throw new \Exception('Journal entry cannot be posted');
        }

        DB::transaction(function () {
            // Create general ledger entries for each line
            foreach ($this->lines as $line) {
                GeneralLedgerEntry::create([
                    'entry_number' => GeneralLedgerEntry::generateEntryNumber(),
                    'account_id' => $line->account_id,
                    'branch_id' => $this->branch_id,
                    'user_id' => $this->user_id,
                    'transaction_date' => $this->transaction_date,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'description' => $line->description,
                    'reference_number' => $this->reference_number,
                    'reference_type' => 'journal_entry',
                    'reference_id' => $this->id,
                    'status' => 'approved', // Auto-approve posted entries
                    'approved_by' => $this->approved_by,
                    'approved_at' => now(),
                    'voucher_number' => $this->journal_number,
                ]);
            }

            // Update journal entry status
            $this->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);
        });
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'posted' => 'success',
            default => 'secondary',
        };
    }

    public function getFormattedTotalDebits()
    {
        return '$' . number_format($this->total_debits, 2);
    }

    public function getFormattedTotalCredits()
    {
        return '$' . number_format($this->total_credits, 2);
    }

    // Static methods
    public static function generateJournalNumber()
    {
        $date = now()->format('Ymd');
        $lastEntry = static::where('journal_number', 'like', "JE{$date}%")
            ->orderBy('journal_number', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->journal_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "JE{$date}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function calculateTotals()
    {
        $totalDebits = $this->lines()->sum('debit');
        $totalCredits = $this->lines()->sum('credit');

        $this->update([
            'total_debits' => $totalDebits,
            'total_credits' => $totalCredits,
        ]);
    }
}
