<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycDocument extends Model
{
    protected $fillable = [
        'client_id',
        'document_type',
        'document_number',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'status',
        'verified_by',
        'verified_at',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'expiry_date' => 'date',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Methods
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isVerified()
    {
        return $this->status === 'verified' && $this->verified_at;
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'verified' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            'expired' => 'secondary',
            default => 'secondary',
        };
    }

    public function getFileSizeFormatted()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
