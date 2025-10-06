<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Staff extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'employee_id',
        'position',
        'salary',
        'hire_date',
        'termination_date',
        'status',
        'payroll_status',
        'bank_account',
        'phone',
        'address',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'termination_date' => 'date',
        'payroll_status' => 'array',
    ];

    /**
     * Get the user that owns the staff record.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payrolls for this staff member.
     */
    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get the latest payroll for this staff member.
     */
    public function latestPayroll()
    {
        return $this->hasOne(Payroll::class)->latest();
    }

    /**
     * Check if staff is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['position', 'salary', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}