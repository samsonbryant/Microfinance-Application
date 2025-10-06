<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecoveryAction extends Model
{
    protected $fillable = [
        'collection_id',
        'action_type',
        'action_date',
        'notes',
        'performed_by',
        'outcome',
        'next_action_date',
        'status',
    ];

    protected $casts = [
        'action_date' => 'datetime',
        'next_action_date' => 'date',
    ];

    // Relationships
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // Methods
    public function getActionTypeText()
    {
        return match($this->action_type) {
            'phone_call' => 'Phone Call',
            'visit' => 'Field Visit',
            'email' => 'Email',
            'sms' => 'SMS',
            'letter' => 'Formal Letter',
            'legal_action' => 'Legal Action',
            'escalation' => 'Escalation',
            'payment_received' => 'Payment Received',
            default => ucfirst(str_replace('_', ' ', $this->action_type)),
        };
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'completed' => 'success',
            'pending' => 'warning',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function getFormattedActionDate()
    {
        return $this->action_date->format('M d, Y H:i');
    }

    public function getFormattedNextActionDate()
    {
        return $this->next_action_date ? $this->next_action_date->format('M d, Y') : 'N/A';
    }
}
