<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['page', 'title', 'subtitle', 'background_path'])]
class HeroSetting extends Model
{
    public static function forPage(string $page): self
    {
        return static::query()->firstOrCreate(
            ['page' => $page],
            [
                'title' => 'Departemen Mitra',
                'subtitle' => 'Pilih lokasi imersi yang selaras dengan kompetensi Anda.',
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

        return Storage::disk('public')->url($path);
    }
}
