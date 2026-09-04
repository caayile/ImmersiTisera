<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'status', 'verification_status', 'rejection_reason', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function participant()
    {
        return $this->hasOne(Participant::class);
    }

    public function mentor()
    {
        return $this->hasOne(Mentor::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMentor(): bool
    {
        return $this->role === 'mentor';
    }

    public function isParticipant(): bool
    {
        return in_array($this->role, ['participant', 'user'], true);
    }

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function roleLabel(): string
    {
        return match (true) {
            $this->isAdmin() => 'Pengelola Program',
            $this->isMentor() => 'Mentor Industri',
            default => 'Dosen',
        };
    }

    public function homeRoute(): string
    {
        return match (true) {
            $this->isAdmin() => 'admin.dashboard',
            $this->isMentor() => 'mentor.dashboard',
            default => 'participant.dashboard',
        };
    }

    public function toApiUser(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->isParticipant() ? 'user' : $this->role,
            'status' => $this->status ?? 'active',
            'verification_status' => $this->verification_status ?? 'verified',
            'phone' => $this->phone,
            'home' => route($this->homeRoute()),
        ];
    }
}
