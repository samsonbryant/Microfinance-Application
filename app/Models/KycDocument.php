<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KycDocument extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id',
        'document_type',
        'document_number',
        'file_path',
        'original_filename',
        'file_size',
        'mime_type',
        'issue_date',
        'expiry_date',
        'issuing_authority',
        'notes',
        'verification_status',
        'verification_notes',
        'uploaded_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'verified_at' => 'datetime',
        'file_size' => 'integer',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Accessors & Mutators
    public function getFormattedFileSizeAttribute()
    {
        if ($this->file_size < 1024) {
            return $this->file_size . ' B';
        } elseif ($this->file_size < 1048576) {
            return round($this->file_size / 1024, 2) . ' KB';
        } else {
            return round($this->file_size / 1048576, 2) . ' MB';
        }
    }

    public function getDocumentTypeNameAttribute()
    {
        return match($this->document_type) {
            'national_id' => 'National ID',
            'passport' => 'Passport',
            'driving_license' => 'Driving License',
            'birth_certificate' => 'Birth Certificate',
            'utility_bill' => 'Utility Bill',
            'bank_statement' => 'Bank Statement',
            'salary_slip' => 'Salary Slip',
            'business_license' => 'Business License',
            'tax_certificate' => 'Tax Certificate',
            'other' => 'Other',
            default => ucfirst(str_replace('_', ' ', $this->document_type)),
        };
    }

    public function getVerificationStatusBadgeAttribute()
    {
        return match($this->verification_status) {
            'verified' => '<span class="badge bg-success">Verified</span>',
            'rejected' => '<span class="badge bg-danger">Rejected</span>',
            'pending' => '<span class="badge bg-warning">Pending</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getIsImageAttribute()
    {
        return in_array($this->mime_type, [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp'
        ]);
    }

    public function getIsPdfAttribute()
    {
        return $this->mime_type === 'application/pdf';
    }

    public function getPublicUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('verification_status', 'rejected');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now());
    }

    // Methods
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date && 
               $this->expiry_date <= now()->addDays($days) && 
               $this->expiry_date > now();
    }

    public function canBeVerified()
    {
        return $this->verification_status === 'pending';
    }

    public function isRequired()
    {
        return in_array($this->document_type, ['national_id', 'utility_bill']);
    }
}