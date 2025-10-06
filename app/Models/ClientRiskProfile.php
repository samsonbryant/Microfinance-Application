<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientRiskProfile extends Model
{
    protected $fillable = [
        'client_id',
        'risk_score',
        'risk_level',
        'risk_factors',
        'last_assessed',
        'assessed_by',
        'notes',
        'recommendations',
    ];

    protected $casts = [
        'risk_score' => 'decimal:2',
        'risk_factors' => 'array',
        'last_assessed' => 'datetime',
        'recommendations' => 'array',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function assessedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    // Methods
    public function getRiskLevelText()
    {
        return match($this->risk_level) {
            'low' => 'Low Risk',
            'medium' => 'Medium Risk',
            'high' => 'High Risk',
            'very_high' => 'Very High Risk',
            default => 'Unknown',
        };
    }

    public function getRiskLevelBadgeClass()
    {
        return match($this->risk_level) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'very_high' => 'dark',
            default => 'secondary',
        };
    }

    public function isLowRisk()
    {
        return $this->risk_level === 'low';
    }

    public function isMediumRisk()
    {
        return $this->risk_level === 'medium';
    }

    public function isHighRisk()
    {
        return $this->risk_level === 'high';
    }

    public function isVeryHighRisk()
    {
        return $this->risk_level === 'very_high';
    }

    public function getFormattedRiskScore()
    {
        return number_format($this->risk_score, 2) . '%';
    }

    public function getFormattedLastAssessed()
    {
        return $this->last_assessed->format('M d, Y H:i');
    }

    public function getRiskFactorsText()
    {
        if (!$this->risk_factors) {
            return 'No risk factors assessed';
        }

        $factors = [];
        foreach ($this->risk_factors as $factor => $score) {
            $factors[] = ucfirst(str_replace('_', ' ', $factor)) . ': ' . number_format($score, 1);
        }

        return implode(', ', $factors);
    }
}
