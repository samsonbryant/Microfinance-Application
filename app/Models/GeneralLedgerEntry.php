<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeneralLedgerEntry extends Model
{
    protected $fillable = [
        'entry_number',
        'account_id',
        'branch_id',
        'user_id',
        'transaction_date',
        'debit',
        'credit',
        'description',
        'reference_number',
        'reference_type',
        'reference_id',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'voucher_number',
        'metadata',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'transaction_date' => 'date',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

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

    public function reference(): MorphTo
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
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

    public function approve($userId)
    {
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

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getFormattedAmount()
    {
        $amount = $this->getAmount();
        return '$' . number_format($amount, 2);
    }

    // Static methods
    public static function generateEntryNumber()
    {
        $date = now()->format('Ymd');
        $lastEntry = static::where('entry_number', 'like', "GL{$date}%")
            ->orderBy('entry_number', 'desc')
            ->first();

        if ($lastEntry) {
            $lastNumber = (int) substr($lastEntry->entry_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "GL{$date}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function getBalanceForAccount($accountId, $asOfDate = null)
    {
        $query = static::where('account_id', $accountId)
            ->where('status', 'approved');

        if ($asOfDate) {
            $query->where('transaction_date', '<=', $asOfDate);
        }

        $entries = $query->get();

        $debits = $entries->sum('debit');
        $credits = $entries->sum('credit');

        $account = ChartOfAccount::find($accountId);
        if ($account && $account->normal_balance === 'debit') {
            return $account->opening_balance + $debits - $credits;
        } else {
            return $account->opening_balance + $credits - $debits;
        }
    }

    public static function getTrialBalance($asOfDate = null)
    {
        $accounts = ChartOfAccount::where('is_active', true)->get();
        $trialBalance = [];

        foreach ($accounts as $account) {
            $balance = static::getBalanceForAccount($account->id, $asOfDate);
            if ($balance != 0) {
                $trialBalance[] = [
                    'account' => $account,
                    'debit' => $account->normal_balance === 'debit' ? $balance : 0,
                    'credit' => $account->normal_balance === 'credit' ? abs($balance) : 0,
                ];
            }
        }

        return $trialBalance;
    }
}
