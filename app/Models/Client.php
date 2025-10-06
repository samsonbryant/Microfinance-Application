<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'client_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'country',
        'occupation',
        'monthly_income',
        'income_currency',
        'kyc_status',
        'status',
        'branch_id',
        'created_by'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'monthly_income' => 'decimal:2',
    ];

    // Accessors
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function savingsAccounts(): HasMany
    {
        return $this->hasMany(SavingsAccount::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function collaterals(): HasMany
    {
        return $this->hasMany(Collateral::class);
    }

    public function kycDocuments(): HasMany
    {
        return $this->hasMany(KycDocument::class);
    }

    // Methods
    public function getTotalLoanAmount(): float
    {
        return $this->loans()
            ->whereIn('status', ['approved', 'disbursed', 'active'])
            ->sum('amount');
    }

    public function getTotalSavingsBalance(): float
    {
        return $this->savingsAccounts()
            ->where('status', 'active')
            ->sum('balance');
    }

    public function getOutstandingLoanBalance(): float
    {
        return $this->loans()
            ->whereIn('status', ['disbursed', 'active', 'overdue'])
            ->sum('outstanding_balance');
    }
}
