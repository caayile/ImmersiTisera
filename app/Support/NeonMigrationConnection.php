<?php

namespace App\Support;

class NeonMigrationConnection
{
    /**
     * Commands that take a schema lock. Neon's pooler cannot run them.
     *
     * @var list<string>
     */
    private const DIRECT_COMMANDS = [
        'migrate',
        'migrate:fresh',
        'migrate:install',
        'migrate:refresh',
        'migrate:reset',
        'migrate:rollback',
        'migrate:status',
        'db:seed',
        'db:wipe',
    ];

    /**
     * Use the direct Neon host for schema commands and keep the pooler for the app.
     *
     * @param  array<string, mixed>  $connection
     * @return array<string, mixed>
     */
    public static function forConsole(array $connection, ?string $command): array
    {
        if (! in_array($command, self::DIRECT_COMMANDS, true)) {
            return $connection;
        }

        $direct = $connection['direct'] ?? null;

        if (! is_array($direct)) {
            return $connection;
        }

        $directUrl = $direct['url'] ?? null;
        $directHost = $direct['host'] ?? null;

        if (blank($directUrl) && blank($directHost)) {
            return $connection;
        }

        if (filled($directUrl)) {
            $connection['url'] = $directUrl;
        } else {
            $connection['url'] = null;
        }

        if (filled($directHost)) {
            $connection['host'] = $directHost;
        }

        foreach (['port', 'username', 'password', 'sslmode'] as $key) {
            if (filled($direct[$key] ?? null)) {
                $connection[$key] = $direct[$key];
            }
        }

        return $connection;
    }
}
