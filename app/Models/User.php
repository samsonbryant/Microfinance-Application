<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use PragmaRX\Google2FA\Google2FA;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'branch_id',
        'is_active',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_recovery_codes' => 'array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /**
     * Get the branch that the user belongs to.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the staff record for this user.
     */
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    /**
     * Get the client record for this user (if borrower).
     */
    public function client()
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Check if user has 2FA enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return !is_null($this->two_factor_secret);
    }

    /**
     * Generate 2FA secret.
     */
    public function generateTwoFactorSecret(): string
    {
        $google2fa = new Google2FA();
        $this->two_factor_secret = $google2fa->generateSecretKey();
        $this->save();
        return $this->two_factor_secret;
    }

    /**
     * Get 2FA QR code URL.
     */
    public function getTwoFactorQrCodeUrl(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->getQRCodeUrl(
            config('app.name'),
            $this->email,
            $this->two_factor_secret
        );
    }

    /**
     * Verify 2FA code.
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        $google2fa = new Google2FA();
        return $google2fa->verifyKey($this->two_factor_secret, $code);
    }

    /**
     * Get activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'role', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
