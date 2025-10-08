<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseEntry extends Model
{
    protected $fillable = [
        'expense_number',
        'branch_id',
        'user_id',
        'account_id',
        'expense_date',
        'amount',
        'reference_number',
        'description',
        'status',
        'approved_by',
        'approved_at',
        'posted_at',
        'rejection_reason',
        'receipt_number',
        'attachments',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
        'attachments' => 'array',
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

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Methods
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
        return $this->status === 'pending';
    }

    public function canBeApproved()
    {
        return $this->status === 'pending';
    }

    public function canBePosted()
    {
        return $this->status === 'approved';
    }

    public function approve($userId)
    {
        if (!$this->canBeApproved()) {
            throw new \Exception('Expense entry cannot be approved');
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
            throw new \Exception('Expense entry cannot be posted');
        }

        DB::transaction(function () {
            // Get cash account (assuming code 1000 for cash on hand)
            $cashAccount = ChartOfAccount::where('code', '1000')->first();
            
            if (!$cashAccount) {
                throw new \Exception('Cash account not found');
            }

            // Create general ledger entries
            // Debit: Expense Account
            GeneralLedgerEntry::create([
                'entry_number' => GeneralLedgerEntry::generateEntryNumber(),
                'account_id' => $this->account_id,
                'branch_id' => $this->branch_id,
                'user_id' => $this->user_id,
                'transaction_date' => $this->expense_date,
                'debit' => $this->amount,
                'credit' => 0,
                'description' => $this->description,
                'reference_number' => $this->reference_number,
                'reference_type' => 'expense_entry',
                'reference_id' => $this->id,
                'status' => 'approved',
                'approved_by' => $this->approved_by,
                'approved_at' => now(),
                'voucher_number' => $this->expense_number,
            ]);

            // Credit: Cash Account
            GeneralLedgerEntry::create([
                'entry_number' => GeneralLedgerEntry::generateEntryNumber(),
                'account_id' => $cashAccount->id,
                'branch_id' => $this->branch_id,
                'user_id' => $this->user_id,
                'transaction_date' => $this->expense_date,
                'debit' => 0,
                'credit' => $this->amount,
                'description' => $this->description,
                'reference_number' => $this->reference_number,
                'reference_type' => 'expense_entry',
                'reference_id' => $this->id,
                'status' => 'approved',
                'approved_by' => $this->approved_by,
                'approved_at' => now(),
                'voucher_number' => $this->expense_number,
            ]);

            // Update expense entry status
            $this->update([
                'status' => 'posted',
                'posted_at' => now(),
            ]);
        });
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'posted' => 'success',
            default => 'secondary',
        };
    }

    public function getFormattedAmount()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function hasAttachments()
    {
        return !empty($this->attachments);
    }

    // Static methods
    public static function generateExpenseNumber()
    {
        $date = now()->format('Ymd');
        $lastEntry = static::where('expense_number', 'like', "EXP{$date}%")
            ->orderBy('expense_number', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->expense_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "EXP{$date}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function getExpensesByAccount($accountId, $startDate = null, $endDate = null)
    {
        $query = static::where('account_id', $accountId)
            ->where('status', 'posted');

        if ($startDate) {
            $query->where('expense_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('expense_date', '<=', $endDate);
        }

        return $query->get();
    }

    public static function getTotalExpensesByAccount($accountId, $startDate = null, $endDate = null)
    {
        return static::getExpensesByAccount($accountId, $startDate, $endDate)->sum('amount');
    }
}
