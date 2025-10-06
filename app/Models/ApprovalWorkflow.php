<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalWorkflow extends Model
{
    protected $fillable = [
        'reference_id',
        'reference_type',
        'current_level',
        'max_level',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'notes',
        'workflow_data',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'workflow_data' => 'array',
    ];

    // Relationships
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
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

    public function isCompleted()
    {
        return $this->current_level >= $this->max_level;
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusText()
    {
        return match($this->status) {
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    public function getCurrentLevelText()
    {
        return "Level {$this->current_level} of {$this->max_level}";
    }

    public function getFormattedApprovedAt()
    {
        return $this->approved_at ? $this->approved_at->format('M d, Y H:i') : 'N/A';
    }

    public function getFormattedRejectedAt()
    {
        return $this->rejected_at ? $this->rejected_at->format('M d, Y H:i') : 'N/A';
    }

    public function canApprove($userId)
    {
        // Check if user can approve at current level
        $user = User::find($userId);
        if (!$user) {
            return false;
        }

        // Simple role-based approval logic
        $requiredRoles = $this->getRequiredRolesForLevel($this->current_level);
        return $user->hasAnyRole($requiredRoles);
    }

    private function getRequiredRolesForLevel($level)
    {
        return match($level) {
            1 => ['loan_officer'],
            2 => ['branch_manager'],
            3 => ['general_manager', 'admin'],
            default => ['admin'],
        };
    }
}
