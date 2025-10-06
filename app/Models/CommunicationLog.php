<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationLog extends Model
{
    protected $fillable = [
        'client_id',
        'type',
        'message',
        'sent_at',
        'sent_by',
        'status',
        'response',
        'response_at',
        'channel',
        'reference_id',
        'reference_type',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'response_at' => 'datetime',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function reference(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    // Methods
    public function getTypeText()
    {
        return match($this->type) {
            'overdue_notification' => 'Overdue Notification',
            'payment_reminder' => 'Payment Reminder',
            'loan_approval' => 'Loan Approval',
            'loan_rejection' => 'Loan Rejection',
            'general' => 'General Communication',
            'marketing' => 'Marketing',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'sent' => 'success',
            'delivered' => 'info',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'secondary',
        };
    }

    public function getChannelText()
    {
        return match($this->channel) {
            'email' => 'Email',
            'sms' => 'SMS',
            'phone' => 'Phone Call',
            'letter' => 'Letter',
            'in_app' => 'In-App Notification',
            default => ucfirst($this->channel),
        };
    }

    public function isDelivered()
    {
        return in_array($this->status, ['sent', 'delivered']);
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function getFormattedSentAt()
    {
        return $this->sent_at->format('M d, Y H:i');
    }

    public function getFormattedResponseAt()
    {
        return $this->response_at ? $this->response_at->format('M d, Y H:i') : 'N/A';
    }
}
