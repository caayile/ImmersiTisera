<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TwoFactorService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private const PERIOD = 30;

    private const WINDOW = 1;

    public static function generateSecret(): string
    {
        return self::base32Encode(random_bytes(20));
    }

    public static function currentCode(string $secret): string
    {
        $key = self::base32Decode($secret);

        if ($key === null || $key === '') {
            return '';
        }

        return self::hotp($key, (int) floor(now()->getTimestamp() / self::PERIOD));
    }

    public static function verifyCode(string $secret, string $code): bool
    {
        $code = preg_replace('/\D/', '', $code) ?? '';

        if (strlen($code) !== 6) {
            return false;
        }

        $key = self::base32Decode($secret);

        if ($key === null || $key === '') {
            return false;
        }

        $timestamp = now()->getTimestamp();

        for ($offset = -self::WINDOW; $offset <= self::WINDOW; $offset++) {
            $counter = (int) floor(($timestamp + ($offset * self::PERIOD)) / self::PERIOD);

            if (hash_equals(self::hotp($key, $counter), $code)) {
                return true;
            }
        }

        return false;
    }

    public static function provisioningUri(User $user, string $secret): string
    {
        $issuer = (string) config('app.name');

        return sprintf(
            'otpauth://totp/%s:%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            rawurlencode($issuer),
            rawurlencode($user->email),
            rawurlencode($secret),
            rawurlencode($issuer),
        );
    }

    /**
     * @return list<string>
     */
    public static function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn (): string => strtoupper(bin2hex(random_bytes(5))))
            ->all();
    }

    /**
     * @param  list<string>  $codes
     * @return list<string>
     */
    public static function hashRecoveryCodes(array $codes): array
    {
        return array_map(fn (string $code): string => Hash::make($code), $codes);
    }

    /**
     * @param  list<string>  $hashes
     * @return string|null The consumed hash, or null when no code matches.
     */
    public static function matchRecoveryCode(array $hashes, string $code): ?string
    {
        $code = strtoupper(trim($code));

        foreach ($hashes as $hash) {
            if (is_string($hash) && Hash::check($code, $hash)) {
                return $hash;
            }
        }

        return null;
    }

    private static function hotp(string $key, int $counter): string
    {
        $message = pack('N2', ($counter >> 32) & 0xFFFFFFFF, $counter & 0xFFFFFFFF);
        $hash = hash_hmac('sha1', $message, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $binary = substr($hash, $offset, 4);
        $value = (unpack('N', $binary) ?: [1 => 0])[1] & 0x7FFFFFFF;

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $binary): string
    {
        $output = '';
        $buffer = 0;
        $bits = 0;

        foreach (unpack('C*', $binary) ?: [] as $byte) {
            $buffer = ($buffer << 8) | $byte;
            $bits += 8;

            while ($bits >= 5) {
                $output .= self::ALPHABET[($buffer >> ($bits - 5)) & 31];
                $bits -= 5;
            }
        }

        if ($bits > 0) {
            $output .= self::ALPHABET[($buffer << (5 - $bits)) & 31];
        }

        return $output;
    }

    private static function base32Decode(string $secret): ?string
    {
        $secret = strtoupper(rtrim($secret, '='));
        $binary = '';
        $buffer = 0;
        $bits = 0;

        foreach (str_split($secret) as $character) {
            $index = strpos(self::ALPHABET, $character);

            if ($index === false) {
                return null;
            }

            $buffer = ($buffer << 5) | $index;
            $bits += 5;

            if ($bits >= 8) {
                $binary .= chr(($buffer >> ($bits - 8)) & 0xFF);
                $bits -= 8;
            }
        }

        return $binary;
    }
}
