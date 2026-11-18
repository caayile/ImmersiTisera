<?php

namespace App\Support;

final class OrganizationName
{
    private const ABBREVIATIONS = [
        'TSPM' => 'Tiga Serangkai Pustaka Mandiri',
        'TSIC' => 'Tiga Serangkai Inti Corpora',
        'WJL' => 'Wangsa Jatra Lestari',
    ];

    private const LEGACY_NAMES = [
        'Tiga Serangkai Pustama Mandiri' => 'Tiga Serangkai Pustaka Mandiri',
        'Tiga Serangkai Innovation Center' => 'Tiga Serangkai Inti Corpora',
        'Wangsa Jastra Lestari' => 'Wangsa Jatra Lestari',
    ];

    /**
     * Expand organization abbreviations and normalize legacy names for display.
     */
    public static function format(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = str_ireplace(
            array_keys(self::LEGACY_NAMES),
            array_values(self::LEGACY_NAMES),
            $value,
        );

        return preg_replace_callback(
            '/\b(TSPM|TSIC|WJL)\b/i',
            static fn (array $matches): string => self::ABBREVIATIONS[strtoupper($matches[1])],
            $value,
        ) ?? $value;
    }
}
