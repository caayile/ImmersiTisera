<?php

namespace App\Models;

use App\Services\MediaStorageService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['page', 'title', 'subtitle', 'background_path'])]
class HeroSetting extends Model
{
    public static function forPage(string $page): self
    {
        return static::query()->firstOrCreate(
            ['page' => $page],
            [
                'title' => 'Departemen Mitra',
                'subtitle' => 'Pilih lokasi magang dosen yang selaras dengan kompetensi Anda.',
            ],
        );
    }

    public function backgroundUrl(): ?string
    {
        return $this->resolveMediaUrl($this->background_path);
    }

    public static function resolveMediaUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'images/')) {
            return asset($path);
        }

        if (str_starts_with($path, MediaStorageService::PATH_PREFIX)) {
            $mediaId = substr($path, strlen(MediaStorageService::PATH_PREFIX));

            return Str::isUuid($mediaId) ? route('media.show', $mediaId) : null;
        }

        if (! Storage::disk('public')->exists($path)) {
            // Last resort: relative path to a file that physically exists
            // under public/ (e.g. legacy uploads).
            if (is_file(public_path($path))) {
                return asset($path);
            }

            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
