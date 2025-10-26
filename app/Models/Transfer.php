<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Transfer extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'transfer_number',
        'transaction_date',
        'from_account_id',
        'to_account_id',
        'from_bank_id',
        'to_bank_id',
        'amount',
        'type',
        'reference_number',
        'description',
        'branch_id',
        'user_id',
        'status',
        'approved_by',
        'approved_at',
        'posted_at',
        'rejection_reason',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'to_account_id');
    }

    public function fromBank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'from_bank_id');
    }

    public function toBank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'to_bank_id');
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

    // Methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isPosted()
    {
        return $this->status === 'posted';
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
            throw new \Exception('Transfer cannot be approved');
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
            throw new \Exception('Transfer cannot be posted');
        }

        DB::transaction(function () {
            $accountingService = app(\App\Services\AccountingService::class);
            
            // Create double entry: Debit To Account, Credit From Account
            $accountingService->createDoubleEntry(
                $this->to_account_id,
                $this->from_account_id,
                $this->amount,
                $this->description,
                $this->id,
                'transfer',
                $this->branch_id,
                $this->user_id,
                $this->transaction_date->format('Y-m-d')
            );

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

    // Static methods
    public static function generateTransferNumber()
    {
        $date = now()->format('Ymd');
        $lastTransfer = static::where('transfer_number', 'like', "TRF{$date}%")
            ->orderBy('transfer_number', 'desc')
            ->first();

        if ($lastTransfer) {
            $lastNumber = (int) substr($lastTransfer->transfer_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "TRF{$date}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['transfer_number', 'amount', 'type', 'status', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

