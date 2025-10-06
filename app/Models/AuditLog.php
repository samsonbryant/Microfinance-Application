<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $fillable = [
        'subject_id',
        'subject_type',
        'causer_id',
        'causer_type',
        'description',
        'properties',
        'event',
        'ip_address',
        'user_agent',
        'branch_id',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    // Relationships
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Methods
    public function getEventText()
    {
        return match($this->event) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'restored' => 'Restored',
            'login' => 'Login',
            'logout' => 'Logout',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => ucfirst($this->event),
        };
    }

    public function getEventBadgeClass()
    {
        return match($this->event) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'danger',
            'restored' => 'warning',
            'login' => 'primary',
            'logout' => 'secondary',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }

    public function getCauserName()
    {
        if ($this->causer) {
            return $this->causer->name ?? 'System';
        }
        return 'System';
    }

    public function getSubjectName()
    {
        if ($this->subject) {
            return $this->subject->name ?? $this->subject->title ?? 'Unknown';
        }
        return 'Unknown';
    }

    public function getFormattedCreatedAt()
    {
        return $this->created_at->format('M d, Y H:i:s');
    }

    public function getPropertiesFormatted()
    {
        if (!$this->properties) {
            return 'No additional data';
        }

        $formatted = [];
        foreach ($this->properties as $key => $value) {
            $formatted[] = ucfirst(str_replace('_', ' ', $key)) . ': ' . (is_array($value) ? json_encode($value) : $value);
        }

        return implode(', ', $formatted);
    }
}
