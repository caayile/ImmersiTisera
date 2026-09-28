<?php

namespace App\Services;

use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\HeroSetting;
use App\Models\HeroSlide;
use App\Models\MediaAsset;
use App\Models\News;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class MediaStorageService
{
    public const PATH_PREFIX = 'database-media/';

    public function store(UploadedFile $file): string
    {
        $content = file_get_contents($file->getRealPath());

        if (! is_string($content)) {
            throw new RuntimeException('Unable to read uploaded image.');
        }

        $media = MediaAsset::create([
            'id' => (string) Str::uuid(),
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'content' => MediaAsset::encodeBinaryContent($content, DB::connection()->getDriverName()),
        ]);

        return self::PATH_PREFIX.$media->getKey();
    }

    public function deleteIfUnreferenced(?string $path): void
    {
        if (! $path || ! str_starts_with($path, self::PATH_PREFIX)) {
            return;
        }

        $mediaId = substr($path, strlen(self::PATH_PREFIX));
        if (! Str::isUuid($mediaId)) {
            return;
        }

        $isReferenced = Department::query()->where('image_path', $path)->exists()
            || BusinessUnit::query()->where('image_path', $path)->exists()
            || HeroSetting::query()->where('background_path', $path)->exists()
            || HeroSlide::query()->where('image_path', $path)->exists()
            || News::query()->where('cover_image', $path)->exists();

        if (! $isReferenced) {
            MediaAsset::query()->whereKey($mediaId)->delete();
        }
    }
}
