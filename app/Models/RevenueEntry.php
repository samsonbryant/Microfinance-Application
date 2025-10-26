<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class RevenueEntry extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'revenue_number',
        'transaction_date',
        'account_id',
        'revenue_type',
        'description',
        'amount',
        'bank_id',
        'reference_number',
        'loan_id',
        'client_id',
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
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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
            throw new \Exception('Revenue entry cannot be approved');
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
            throw new \Exception('Revenue entry cannot be posted');
        }

        DB::transaction(function () {
            $accountingService = app(\App\Services\AccountingService::class);
            
            // Determine receiving account (cash or bank)
            $receivingAccountId = $this->getReceivingAccountId();
            
            // Create double entry: Debit Cash/Bank Account, Credit Revenue Account
            $accountingService->createDoubleEntry(
                $receivingAccountId,
                $this->account_id,
                $this->amount,
                $this->description,
                $this->id,
                'revenue',
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

    private function getReceivingAccountId()
    {
        if ($this->bank_id && $this->bank) {
            return $this->bank->account_id;
        }
        
        return ChartOfAccount::where('code', '1000')->first()->id ?? null; // Cash on Hand
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
    public static function generateRevenueNumber()
    {
        $date = now()->format('Ymd');
        $lastRevenue = static::where('revenue_number', 'like', "REV{$date}%")
            ->orderBy('revenue_number', 'desc')
            ->first();

        if ($lastRevenue) {
            $lastNumber = (int) substr($lastRevenue->revenue_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "REV{$date}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['revenue_number', 'amount', 'revenue_type', 'status', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

