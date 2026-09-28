<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use Illuminate\Http\Response;

class MediaController extends Controller
{
    public function show(MediaAsset $mediaAsset): Response
    {
        return response($mediaAsset->binaryContent(), 200, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Content-Type' => $mediaAsset->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
