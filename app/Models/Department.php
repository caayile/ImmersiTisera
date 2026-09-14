<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'subtitle', 'description', 'function', 'area', 'map_url', 'image_path', 'status'])]
class Department extends Model
{
    public function imageUrl(): ?string
    {
        return HeroSetting::resolveMediaUrl($this->image_path);
    }

    public function businessUnits()
    {
        return $this->hasMany(BusinessUnit::class);
    }

    public function mentors()
    {
        return $this->hasMany(Mentor::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function isDirectPlacement(): bool
    {
        $units = $this->relationLoaded('businessUnits')
            ? $this->businessUnits
            : $this->businessUnits()->get();

        return $units->count() === 1 && $units->first()->name === $this->name;
    }

    public function primaryUnit(): ?BusinessUnit
    {
        $units = $this->relationLoaded('businessUnits')
            ? $this->businessUnits
            : $this->businessUnits();

        if ($units instanceof HasMany) {
            return $units->where('status', 'open')->first();
        }

        return $units->firstWhere('status', 'open') ?? $units->first();
    }

    /**
     * @return array{lat: string, lng: string}|null
     */
    public static function mapCoordinates(?string $url): ?array
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $resolved = $url;
        if (! str_contains($url, 'output=embed') && (str_contains($url, 'maps.app.goo.gl') || str_contains($url, 'goo.gl/maps'))) {
            $resolved = self::followMapRedirects($url) ?? $url;
        }

        $candidates = [$resolved, $url];

        foreach ($candidates as $candidate) {
            if (preg_match_all('/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/', $candidate, $matches, PREG_SET_ORDER) && $matches !== []) {
                $match = $matches[array_key_last($matches)];

                return self::validCoordinates($match[1], $match[2]);
            }

            if (preg_match('/[?&](?:q|query|ll|center)=(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/i', $candidate, $match)) {
                return self::validCoordinates($match[1], $match[2]);
            }

            if (preg_match('/\/place\/(-?\d+(?:\.\d+)?)[,+](-?\d+(?:\.\d+)?)/', $candidate, $match)) {
                return self::validCoordinates($match[1], $match[2]);
            }

            if (preg_match('/\/(?:search|dir)\/(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/', $candidate, $match)) {
                return self::validCoordinates($match[1], $match[2]);
            }
        }

        foreach ($candidates as $candidate) {
            if (preg_match('/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)/', $candidate, $match)) {
                return self::validCoordinates($match[1], $match[2]);
            }
        }

        return null;
    }

    public static function embedMapUrl(?string $url): ?string
    {
        $coordinates = self::mapCoordinates($url);
        if ($coordinates !== null) {
            return self::embedUrlFromCoordinates($coordinates['lat'], $coordinates['lng']);
        }

        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (str_contains($url, 'output=embed')) {
            return $url;
        }

        $resolved = self::followMapRedirects($url) ?? $url;
        if (str_contains($resolved, 'google.com/maps') || str_contains($resolved, 'maps.google')) {
            $separator = str_contains($resolved, '?') ? '&' : '?';

            return $resolved.$separator.'output=embed';
        }

        return null;
    }

    public static function openMapUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        $isEmbed = str_contains($url, 'output=embed');
        if (! $isEmbed && self::isGoogleMapsUrl($url)) {
            return $url;
        }

        $coordinates = self::mapCoordinates($url);
        if ($coordinates !== null) {
            return self::externalUrlFromCoordinates($coordinates['lat'], $coordinates['lng']);
        }

        if ($isEmbed) {
            return str_replace(['&output=embed', '?output=embed'], '', $url);
        }

        return null;
    }

    public function mapEmbedSrc(): string
    {
        $embed = self::embedMapUrl($this->map_url);
        if ($embed !== null) {
            return $embed;
        }

        return 'https://www.google.com/maps?q='.rawurlencode($this->mapFallbackQuery()).'&z=15&hl=id&output=embed';
    }

    public function mapExternalUrl(): string
    {
        $open = self::openMapUrl($this->map_url);
        if ($open !== null) {
            return $open;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($this->mapFallbackQuery());
    }

    /**
     * @return array{lat: string, lng: string}|null
     */
    private static function validCoordinates(string $lat, string $lng): ?array
    {
        $latitude = (float) $lat;
        $longitude = (float) $lng;

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return null;
        }

        return ['lat' => $lat, 'lng' => $lng];
    }

    private static function embedUrlFromCoordinates(string $lat, string $lng): string
    {
        return "https://maps.google.com/maps?q={$lat},{$lng}&hl=id&z=17&output=embed";
    }

    private static function externalUrlFromCoordinates(string $lat, string $lng): string
    {
        return "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}";
    }

    private static function isGoogleMapsUrl(string $url): bool
    {
        return str_contains($url, 'google.com/maps')
            || str_contains($url, 'maps.google')
            || str_contains($url, 'maps.app.goo.gl')
            || str_contains($url, 'goo.gl/maps');
    }

    private function mapFallbackQuery(): string
    {
        return trim($this->name.' '.($this->area ?: 'Solo, Indonesia'));
    }

    private static function followMapRedirects(string $url): ?string
    {
        $context = stream_context_create([
            'http' => ['follow_location' => 0, 'timeout' => 8],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $hops = 0;
        while ($hops++ < 6) {
            $headers = @get_headers($url, 1, $context);
            if (! $headers) {
                return null;
            }

            $location = $headers['Location'] ?? null;
            if (! $location) {
                return $url;
            }

            $url = is_array($location) ? end($location) : $location;
        }

        return $url;
    }
}
