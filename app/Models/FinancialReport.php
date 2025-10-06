<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialReport extends Model
{
    protected $fillable = [
        'report_type',
        'title',
        'description',
        'data',
        'generated_by',
        'branch_id',
        'status',
        'generated_at',
    ];

    protected $casts = [
        'data' => 'array',
        'generated_at' => 'datetime',
    ];

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
