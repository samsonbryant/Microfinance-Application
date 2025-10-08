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
        'category',
        'parent_id',
        'is_active',
        'description',
        'normal_balance',
        'opening_balance',
        'currency',
        'is_system_account',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system_account' => 'boolean',
        'opening_balance' => 'decimal:2',
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

    // Enhanced methods for Microbook-G5
    public function getCategoryBadgeClass()
    {
        return match($this->category) {
            'cash_on_hand', 'cash_in_bank' => 'success',
            'loan_portfolio', 'accounts_receivable' => 'primary',
            'property_plant_equipment' => 'info',
            'accumulated_depreciation' => 'secondary',
            'client_savings', 'interest_payable' => 'warning',
            'loan_interest_income', 'penalty_income' => 'success',
            'salaries_wages', 'rent_expense' => 'danger',
            default => 'secondary',
        };
    }

    public function isDebitAccount()
    {
        return $this->normal_balance === 'debit';
    }

    public function isCreditAccount()
    {
        return $this->normal_balance === 'credit';
    }

    public function getCurrentBalance()
    {
        $debits = $this->generalLedgers()->sum('debit');
        $credits = $this->generalLedgers()->sum('credit');
        
        if ($this->normal_balance === 'debit') {
            return $this->opening_balance + $debits - $credits;
        } else {
            return $this->opening_balance + $credits - $debits;
        }
    }

    public function getFormattedCurrentBalance()
    {
        return number_format($this->getCurrentBalance(), 2);
    }

    // Static methods for account type validation
    public static function getNormalBalanceForType($type)
    {
        return match($type) {
            'asset', 'expense' => 'debit',
            'liability', 'equity', 'revenue' => 'credit',
            default => 'debit',
        };
    }

    public static function getAccountsByType($type)
    {
        return static::where('type', $type)->where('is_active', true)->get();
    }

    public static function getSystemAccounts()
    {
        return static::where('is_system_account', true)->get();
    }

    public function canBeDeleted()
    {
        return !$this->is_system_account && $this->generalLedgers()->count() === 0;
    }
}
