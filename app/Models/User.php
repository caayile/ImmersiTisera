<?php

namespace App\Models;

use App\Services\MediaStorageService;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'google_id', 'avatar', 'password', 'role', 'status', 'verification_status', 'rejection_reason', 'phone'])]
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

    public function isUser(): bool
    {
        return $this->isParticipant();
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
            default => 'home',
        };
    }

    /**
     * URL foto profil yang siap dipakai di <img>/API. Mengembalikan null
     * bila nilai tersimpan menunjuk ke file yang sudah tidak ada, sehingga
     * tampilan jatuh ke inisial nama alih-alih ikon gambar rusak.
     */
    public function avatarUrl(): ?string
    {
        $avatar = trim((string) $this->avatar);

        if ($avatar === '') {
            return null;
        }

        if (str_starts_with($avatar, MediaStorageService::PATH_PREFIX)) {
            return HeroSetting::resolveMediaUrl($avatar);
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            // URL absolut milik aplikasi ini (/storage/avatars/...) → pastikan
            // file-nya masih ada, kalau tidak anggap tidak punya foto.
            if (preg_match('#/storage/(avatars/.+)$#', $avatar, $match)) {
                return Storage::disk('public')->exists($match[1])
                    ? Storage::disk('public')->url($match[1])
                    : null;
            }

            // URL eksternal (Google, ui-avatars, dsb.) dipakai apa adanya.
            return $avatar;
        }

        // Format lawas relatif: avatars/... atau storage/avatars/...
        $path = ltrim(preg_replace('#^storage/#', '', ltrim($avatar, '/')), '/');

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        if (is_file(public_path($path))) {
            return asset($path);
        }

        return null;
    }

    public function notificationsRoute(): string
    {
        return $this->isMentor() ? 'mentor.notifications' : 'participant.notifications';
    }

    public function toApiUser(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatarUrl(),
            'role' => $this->isParticipant() ? 'user' : $this->role,
            'status' => $this->status ?? 'active',
            'verification_status' => $this->verification_status ?? 'verified',
            'phone' => $this->phone,
            'home' => route($this->homeRoute()),
        ];
    }
}
