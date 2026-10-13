<?php

namespace App\Support;

class NotificationLink
{
    public static function normalize(?string $url): ?string
    {
        if (! filled($url)) {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $parts = parse_url($url);
            $url = ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
        } elseif (preg_match('#^(?:localhost|127\.0\.0\.1)(?::\d+)?(/.*)$#', $url, $matches) === 1) {
            $url = $matches[1];
        }

        if (! str_starts_with($url, '/')) {
            $url = '/'.$url;
        }

        return $url;
    }

    public static function href(?string $url, ?string $fallback = null): string
    {
        $path = self::normalize($url ?? $fallback) ?? self::normalize($fallback) ?? '/';

        if (! str_starts_with($path, '/')) {
            $path = '/'.$path;
        }

        // Path relatif supaya port dev (mis. :8000) tidak hilang saat diklik.
        return $path;
    }
}
