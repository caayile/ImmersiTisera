<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use App\Services\ImageSecurityService;
use Illuminate\Http\Response;

class MediaController extends Controller
{
    public function show(MediaAsset $mediaAsset): Response
    {
        $binary = $mediaAsset->binaryContent();
        $mime = $this->detectMime($binary);

        if (! in_array($mime, ImageSecurityService::allowedMimes(), true)) {
            abort(404);
        }

        return response($binary, 200, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function detectMime(string $binary): ?string
    {
        if ($binary === '' || ! function_exists('finfo_buffer')) {
            return null;
        }

        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($fileInfo === false) {
            return null;
        }

        $mime = finfo_buffer($fileInfo, $binary);
        finfo_close($fileInfo);

        return is_string($mime) ? strtolower($mime) : null;
    }
}
