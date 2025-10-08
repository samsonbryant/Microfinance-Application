<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NextOfKin extends Model
{
    protected $table = 'next_of_kin';

    protected $fillable = [
        'client_id',
        'first_name',
        'last_name',
        'relationship',
        'phone',
        'phone_country',
        'email',
        'address',
        'city',
        'state',
        'zip_code',
        'identification_type',
        'identification_number',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
