<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

#[Fillable([
    'email',
    'name',
    'password',
    'role',
    'phone',
    'institution',
    'nik',
    'department_id',
    'otp',
    'otp_hash',
    'attempts',
    'expires_at',
])]
#[Hidden(['password', 'otp', 'otp_hash'])]
class RegistrationOtp extends Model
{
    public const MAX_ATTEMPTS = 5;

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function hasExceededAttempts(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    public function matchesOtp(string $otp): bool
    {
        return filled($this->otp_hash) && Hash::check($otp, (string) $this->otp_hash);
    }

    public function registerFailedAttempt(): void
    {
        $this->increment('attempts');
    }
}
