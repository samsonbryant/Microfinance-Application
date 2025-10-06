<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'state',
        'country',
        'phone',
        'email',
        'manager_name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
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

    public function loanApplications(): HasMany
    {
        return $this->hasMany(LoanApplication::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    // Methods
    public function getTotalClients(): int
    {
        return $this->clients()->count();
    }

    public function getActiveClients(): int
    {
        return $this->clients()->where('status', 'active')->count();
    }

    public function getTotalLoans(): int
    {
        return $this->loans()->count();
    }

    public function getActiveLoans(): int
    {
        return $this->loans()->whereIn('status', ['active', 'disbursed'])->count();
    }

    public function getTotalLoanPortfolio(): float
    {
        return $this->loans()->whereIn('status', ['active', 'disbursed', 'overdue'])->sum('outstanding_balance');
    }

    public function getTotalSavings(): float
    {
        return $this->savingsAccounts()->where('status', 'active')->sum('balance');
    }

    public function getOverdueLoans(): int
    {
        return $this->loans()->where('status', 'overdue')->count();
    }

    public function getPortfolioAtRisk(): float
    {
        return $this->loans()->where('status', 'overdue')->sum('outstanding_balance');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByRegion($query, $region)
    {
        return $query->where('state', $region);
    }
}
