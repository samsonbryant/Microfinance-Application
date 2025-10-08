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
        'primary_phone_country',
        'secondary_phone',
        'secondary_phone_country',
        'date_of_birth',
        'gender',
        'marital_status',
        'identification_type',
        'identification_number',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'occupation',
        'employer',
        'employee_number',
        'tax_number',
        'monthly_income',
        'income_currency',
        'avatar',
        'files',
        'kyc_status',
        'status',
        'branch_id',
        'created_by'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'monthly_income' => 'decimal:2',
        'files' => 'array',
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

    public function nextOfKin(): HasMany
    {
        return $this->hasMany(NextOfKin::class);
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
