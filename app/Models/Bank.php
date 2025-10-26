<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Bank extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'type',
        'account_id',
        'account_number',
        'swift_code',
        'branch_name',
        'address',
        'contact_person',
        'phone',
        'email',
        'current_balance',
        'is_active',
        'description',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function revenueEntries(): HasMany
    {
        return $this->hasMany(RevenueEntry::class);
    }

    public function transfersFrom(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_bank_id');
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_bank_id');
    }

    // Methods
    public function getFormattedBalance()
    {
        return number_format($this->current_balance, 2);
    }

    public function updateBalance()
    {
        if ($this->account_id) {
            $this->current_balance = $this->account->getCurrentBalance();
            $this->save();
        }
    }

    public function getTypeBadgeClass()
    {
        return match($this->type) {
            'cash' => 'success',
            'bank' => 'primary',
            'mobile_money' => 'info',
            default => 'secondary',
        };
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'type', 'current_balance', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

