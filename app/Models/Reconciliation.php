<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reconciliation extends Model
{
    protected $fillable = [
        'reconciliation_number',
        'type',
        'account_id',
        'branch_id',
        'user_id',
        'reconciliation_date',
        'system_balance',
        'actual_balance',
        'variance',
        'status',
        'notes',
        'reconciliation_items',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'system_balance' => 'decimal:2',
        'actual_balance' => 'decimal:2',
        'variance' => 'decimal:2',
        'reconciliation_date' => 'date',
        'approved_at' => 'datetime',
        'reconciliation_items' => 'array',
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

    public function items(): HasMany
    {
        return $this->hasMany(ReconciliationItem::class);
    }

    // Methods
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isBalanced()
    {
        return abs($this->variance) < 0.01;
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'in_progress' => 'warning',
            'completed' => 'info',
            'approved' => 'success',
            default => 'secondary',
        };
    }

    public function getFormattedSystemBalance()
    {
        return '$' . number_format($this->system_balance, 2);
    }

    public function getFormattedActualBalance()
    {
        return '$' . number_format($this->actual_balance, 2);
    }

    public function getFormattedVariance()
    {
        $formatted = '$' . number_format(abs($this->variance), 2);
        return $this->variance >= 0 ? $formatted : '-' . $formatted;
    }

    public function getVarianceClass()
    {
        if (abs($this->variance) < 0.01) {
            return 'text-success';
        } elseif ($this->variance > 0) {
            return 'text-warning';
        } else {
            return 'text-danger';
        }
    }

    public function calculateVariance()
    {
        $this->variance = $this->actual_balance - $this->system_balance;
        $this->save();
    }

    public function approve($userId)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }

    public function getMatchedItems()
    {
        return $this->items()->where('status', 'matched')->get();
    }

    public function getUnmatchedItems()
    {
        return $this->items()->where('status', 'unmatched')->get();
    }

    public function getDisputedItems()
    {
        return $this->items()->where('status', 'disputed')->get();
    }

    // Static methods
    public static function generateReconciliationNumber()
    {
        $date = now()->format('Ymd');
        $lastReconciliation = static::where('reconciliation_number', 'like', "REC{$date}%")
            ->orderBy('reconciliation_number', 'desc')
            ->first();

        if ($lastReconciliation) {
            $lastNumber = (int) substr($lastReconciliation->reconciliation_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "REC{$date}" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public static function getReconciliationsByType($type, $startDate = null, $endDate = null)
    {
        $query = static::where('type', $type);

        if ($startDate) {
            $query->where('reconciliation_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('reconciliation_date', '<=', $endDate);
        }

        return $query->orderBy('reconciliation_date', 'desc')->get();
    }
}
