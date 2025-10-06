<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collateral extends Model
{
    protected $fillable = [
        'client_id',
        'type',
        'description',
        'value',
        'location',
        'condition',
        'ownership_document',
        'valuation_date',
        'valued_by',
        'status',
        'notes',
        'documents',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'valuation_date' => 'date',
        'documents' => 'array',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function valuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valued_by');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    // Methods
    public function getLtvRatio($loanAmount)
    {
        if ($this->value <= 0) {
            return 0;
        }
        
        return ($loanAmount / $this->value) * 100;
    }

    public function isAcceptableLtv($loanAmount, $maxLtv = 80)
    {
        return $this->getLtvRatio($loanAmount) <= $maxLtv;
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'active' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            'expired' => 'secondary',
            default => 'secondary',
        };
    }
}
