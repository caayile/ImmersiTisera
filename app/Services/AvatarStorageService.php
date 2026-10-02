<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AvatarStorageService
{
    public function __construct(private readonly MediaStorageService $mediaStorage) {}

    public function store(UploadedFile $file, ?string $previousAvatar): string
    {
        // Simpan ke database-media agar tidak bergantung pada symlink
        // public/storage (kasus foto hilang di instalasi baru).
        $path = $this->mediaStorage->store($file);
        $this->delete($previousAvatar);

        return $path;
    }

    public function delete(?string $avatar): void
    {
        if (! $avatar) {
            return;
        }

        if (str_starts_with($avatar, MediaStorageService::PATH_PREFIX)) {
            $this->mediaStorage->deleteIfUnreferenced($avatar);

            return;
        }

        // Bersihkan file lama (format lawas: avatars/... atau .../storage/avatars/...).
        if (preg_match('#(?:^|/storage/)(avatars/.+)$#', $avatar, $match)) {
            Storage::disk('public')->delete($match[1]);
        }
    }
}
