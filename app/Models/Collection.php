<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    protected $fillable = [
        'loan_id',
        'client_id',
        'overdue_amount',
        'penalty_amount',
        'total_due',
        'days_overdue',
        'status',
        'assigned_to',
        'collection_notes',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'overdue_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'total_due' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function recoveryActions(): HasMany
    {
        return $this->hasMany(RecoveryAction::class);
    }

    // Methods
    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isEscalated()
    {
        return $this->status === 'escalated';
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'resolved' => 'success',
            'pending' => 'warning',
            'escalated' => 'danger',
            'legal' => 'dark',
            default => 'secondary',
        };
    }

    public function getTotalDueFormatted()
    {
        return number_format($this->total_due, 2);
    }

    public function getOverdueAmountFormatted()
    {
        return number_format($this->overdue_amount, 2);
    }

    public function getPenaltyAmountFormatted()
    {
        return number_format($this->penalty_amount, 2);
    }

    public function getDaysOverdueText()
    {
        if ($this->days_overdue <= 7) {
            return "{$this->days_overdue} days";
        } elseif ($this->days_overdue <= 30) {
            return "{$this->days_overdue} days";
        } else {
            return "{$this->days_overdue} days (Escalated)";
        }
    }
}
