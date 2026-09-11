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

    public static function embedMapUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }

        if (str_contains($url, 'output=embed')) {
            return $url;
        }

        $resolved = self::followMapRedirects($url) ?? $url;

        if (preg_match('/@(-?[\d.]+),(-?[\d.]+)/', $resolved, $m) || preg_match('/@(-?[\d.]+),(-?[\d.]+)/', $url, $m)) {
            return "https://maps.google.com/maps?q={$m[1]},{$m[2]}&z=17&output=embed";
        }

        if (str_contains($resolved, 'google.com/maps') || str_contains($resolved, 'maps.google')) {
            return $resolved;
        }

        return null;
    }

    private static function followMapRedirects(string $url): ?string
    {
        $context = stream_context_create([
            'http' => ['follow_location' => 0, 'timeout' => 8],
            'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
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
