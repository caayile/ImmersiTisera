<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * Cache token lookup supaya request API berikutnya tidak menunggu database.
     * Token tetap diverifikasi dengan hash_equals; pencabutan tertunda maksimal 15 detik.
     */
    public static function findToken($token)
    {
        if (! is_string($token) || ! str_contains($token, '|')) {
            return parent::findToken($token);
        }

        [$id, $plainToken] = explode('|', $token, 2);

        if (! ctype_digit($id) || $plainToken === '') {
            return null;
        }

        $hash = hash('sha256', $plainToken);
        $cacheKey = 'sanctum.token.v2:'.$id.':'.substr($hash, 0, 16);

        $record = Cache::remember($cacheKey, now()->addSeconds(15), function () use ($id) {
            $accessToken = static::with('tokenable')->find($id);

            if (! $accessToken) {
                return null;
            }

            $tokenable = $accessToken->tokenable;

            return [
                'name' => $accessToken->name,
                'token' => $accessToken->token,
                'tokenable_type' => $accessToken->getRawOriginal('tokenable_type'),
                'tokenable_id' => $accessToken->tokenable_id,
                'abilities' => $accessToken->abilities,
                'created_at' => $accessToken->created_at?->toDateTimeString(),
                'expires_at' => $accessToken->expires_at?->toDateTimeString(),
                'tokenable_class' => $tokenable ? get_class($tokenable) : null,
                'tokenable_attributes' => $tokenable?->getAttributes() ?? [],
            ];
        });

        if (! $record || ! hash_equals($record['token'], $hash)) {
            return null;
        }

        $accessToken = new static();
        $accessToken->exists = true;
        $accessToken->forceFill([
            'id' => (int) $id,
            'name' => $record['name'],
            'token' => $record['token'],
            'tokenable_type' => $record['tokenable_type'],
            'tokenable_id' => $record['tokenable_id'],
            'abilities' => $record['abilities'],
            'created_at' => $record['created_at'],
            'expires_at' => $record['expires_at'],
        ]);

        if ($record['tokenable_class']) {
            $tokenableClass = $record['tokenable_class'];
            $tokenable = new $tokenableClass();
            $tokenable->exists = true;
            $tokenable->forceFill($record['tokenable_attributes']);
            $accessToken->setRelation('tokenable', $tokenable);
        }

        return $accessToken;
    }
}
