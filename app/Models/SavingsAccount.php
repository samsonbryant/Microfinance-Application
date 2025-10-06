<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsAccount extends Model
{
    protected $fillable = [
        'account_number',
        'client_id',
        'branch_id',
        'account_type',
        'balance',
        'interest_rate',
        'minimum_balance',
        'status',
        'opening_date',
        'maturity_date',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'minimum_balance' => 'decimal:2',
        'opening_date' => 'date',
        'maturity_date' => 'date',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Methods
    public function deposit(float $amount): bool
    {
        $this->balance += $amount;
        return $this->save();
    }

    public function withdraw(float $amount): bool
    {
        if ($this->balance - $amount >= $this->minimum_balance) {
            $this->balance -= $amount;
            return $this->save();
        }
        return false;
    }

    public function calculateInterest(): float
    {
        return $this->balance * ($this->interest_rate / 100) / 12; // Monthly interest
    }

    public function isMatured(): bool
    {
        return $this->maturity_date && $this->maturity_date <= now();
    }

    public function canWithdraw(float $amount): bool
    {
        return $this->status === 'active' && 
               ($this->balance - $amount) >= $this->minimum_balance;
    }
}
