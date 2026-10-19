<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

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
    'expires_at',
])]
#[Hidden(['password', 'otp'])]
class RegistrationOtp extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
