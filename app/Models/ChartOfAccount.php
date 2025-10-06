<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'parent_id',
        'is_active',
        'description',
        'normal_balance',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function generalLedgers(): HasMany
    {
        return $this->hasMany(GeneralLedger::class, 'account_id');
    }

    // Methods
    public function getBalance()
    {
        $debits = $this->generalLedgers->sum('debit');
        $credits = $this->generalLedgers->sum('credit');
        
        if ($this->normal_balance === 'debit') {
            return $debits - $credits;
        } else {
            return $credits - $debits;
        }
    }

    public function getFormattedBalance()
    {
        return number_format($this->getBalance(), 2);
    }

    public function isAsset()
    {
        return $this->type === 'asset';
    }

    public function isLiability()
    {
        return $this->type === 'liability';
    }

    public function isEquity()
    {
        return $this->type === 'equity';
    }

    public function isRevenue()
    {
        return $this->type === 'revenue';
    }

    public function isExpense()
    {
        return $this->type === 'expense';
    }

    public function getTypeBadgeClass()
    {
        return match($this->type) {
            'asset' => 'primary',
            'liability' => 'warning',
            'equity' => 'info',
            'revenue' => 'success',
            'expense' => 'danger',
            default => 'secondary',
        };
    }
}
