<?php

use Illuminate\Support\Str;
use Pdo\Mysql;

if (! function_exists('database_connection_env')) {
    /**
     * Read an engine-specific setting without leaking the default driver's
     * host, port, or credentials into the unused PostgreSQL or MySQL connection.
     */
    function database_connection_env(string $connection, string $key, mixed $default): mixed
    {
        $prefix = match ($connection) {
            'pgsql' => 'PGSQL',
            'mysql' => 'MYSQL',
            default => strtoupper($connection),
        };

        $dedicated = env($prefix.'_'.$key);

        if ($dedicated !== null && $dedicated !== '') {
            return $dedicated;
        }

        if (env('DB_CONNECTION', 'pgsql') === $connection) {
            return env('DB_'.$key, $default);
        }

        return $default;
    }
}

if (! function_exists('neon_database_config')) {
    /**
     * Build the Neon Postgres connection, deriving the direct host from a pooled URL.
     *
     * @return array<string, mixed>
     */
    function neon_database_config(?string $url = null): array
    {
        $urlIsExplicit = func_num_args() === 1;
        $url ??= env('NEON_DATABASE_URL');
        $parts = is_string($url) && $url !== '' ? parse_url($url) : false;
        $host = $urlIsExplicit
            ? (is_array($parts) ? ($parts['host'] ?? null) : null)
            : env('NEON_HOST', is_array($parts) ? ($parts['host'] ?? null) : null);
        $isPooled = is_string($host) && str_contains($host, '-pooler');
        $directUrl = $urlIsExplicit ? null : env('NEON_DIRECT_URL');
        $directHost = $urlIsExplicit
            ? ($isPooled && is_string($host) ? str_replace('-pooler', '', $host) : null)
            : env('NEON_DIRECT_HOST', $isPooled && is_string($host)
                ? str_replace('-pooler', '', $host)
                : null);
        $pooled = $urlIsExplicit
            ? $isPooled
            : filter_var(env('NEON_POOLED', $isPooled), FILTER_VALIDATE_BOOL);

        if ($pooled !== true) {
            $directUrl = null;
            $directHost = null;
            $hasDirect = false;
        } else {
            $hasDirect = filled($directUrl) || filled($directHost);
        }

        return [
            'driver' => 'pgsql',
            'url' => $url ?: null,
            'host' => $host,
            'port' => env('NEON_PORT', is_array($parts) ? (string) ($parts['port'] ?? '5432') : '5432'),
            'database' => env('NEON_DATABASE', is_array($parts) ? ltrim((string) ($parts['path'] ?? 'neondb'), '/') : 'neondb'),
            'username' => env('NEON_USERNAME', is_array($parts) ? ($parts['user'] ?? null) : null),
            'password' => env('NEON_PASSWORD', is_array($parts) && isset($parts['pass']) ? urldecode($parts['pass']) : null),
            'charset' => env('NEON_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('NEON_SSLMODE', 'require'),
            'pooled' => $pooled,
            'direct' => array_filter([
                'url' => $directUrl,
                'host' => $directHost,
                'port' => env('NEON_DIRECT_PORT'),
                'username' => env('NEON_DIRECT_USERNAME'),
                'password' => env('NEON_DIRECT_PASSWORD'),
                'sslmode' => env('NEON_DIRECT_SSLMODE', $hasDirect ? 'require' : null),
            ]),
        ];
    }
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'pgsql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('MYSQL_URL'),
            'host' => database_connection_env('mysql', 'HOST', '127.0.0.1'),
            'port' => database_connection_env('mysql', 'PORT', '3306'),
            'database' => database_connection_env('mysql', 'DATABASE', 'imersi'),
            'username' => database_connection_env('mysql', 'USERNAME', 'root'),
            'password' => database_connection_env('mysql', 'PASSWORD', ''),
            'unix_socket' => env('MYSQL_SOCKET', env('DB_SOCKET', '')),
            'charset' => env('MYSQL_CHARSET', env('DB_CHARSET', 'utf8mb4')),
            'collation' => env('MYSQL_COLLATION', env('DB_COLLATION', 'utf8mb4_unicode_ci')),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('PGSQL_URL', env('DB_CONNECTION') === 'pgsql' ? env('DB_URL') : null),
            'host' => database_connection_env('pgsql', 'HOST', '127.0.0.1'),
            'port' => database_connection_env('pgsql', 'PORT', '5432'),
            'database' => database_connection_env('pgsql', 'DATABASE', 'imersi'),
            'username' => database_connection_env('pgsql', 'USERNAME', 'postgres'),
            'password' => database_connection_env('pgsql', 'PASSWORD', ''),
            'charset' => env('PGSQL_CHARSET', env('DB_CHARSET', 'utf8')),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env('PGSQL_SSLMODE', env('DB_SSLMODE', 'prefer')),
            'pooled' => filter_var(env('PGSQL_POOLED', env('DB_CONNECTION') === 'pgsql' ? env('DB_POOLED', false) : false), FILTER_VALIDATE_BOOL),
            'direct' => array_filter([
                'host' => env('PGSQL_DIRECT_HOST', env('DB_CONNECTION') === 'pgsql' ? env('DB_DIRECT_HOST') : null),
                'port' => env('PGSQL_DIRECT_PORT', env('DB_CONNECTION') === 'pgsql' ? env('DB_DIRECT_PORT') : null),
                'username' => env('PGSQL_DIRECT_USERNAME', env('DB_CONNECTION') === 'pgsql' ? env('DB_DIRECT_USERNAME') : null),
                'password' => env('PGSQL_DIRECT_PASSWORD', env('DB_CONNECTION') === 'pgsql' ? env('DB_DIRECT_PASSWORD') : null),
                'sslmode' => env('PGSQL_DIRECT_SSLMODE', env('DB_CONNECTION') === 'pgsql' ? env('DB_DIRECT_SSLMODE') : null),
            ]),
        ],

        'neon' => neon_database_config(),

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];
