<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AvatarStorageService
{
    public function store(UploadedFile $file, ?string $previousAvatar): string
    {
        $disk = Storage::disk('public');
        $path = $file->store('avatars', 'public');

        if ($previousAvatar && str_starts_with($previousAvatar, $disk->url('avatars/'))) {
            $disk->delete('avatars/'.substr($previousAvatar, strlen($disk->url('avatars/'))));
        }

        return $disk->url($path);
    }
}
