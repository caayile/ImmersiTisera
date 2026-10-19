<?php

namespace Tests\Unit;

use App\Support\NeonMigrationConnection;
use PHPUnit\Framework\TestCase;

class NeonMigrationConnectionTest extends TestCase
{
    public function test_migrate_uses_the_direct_url_instead_of_the_pooler(): void
    {
        $connection = NeonMigrationConnection::forConsole($this->pooledConnection(), 'migrate');

        $this->assertSame('postgresql://imersi:secret@ep-example.ap-southeast-1.aws.neon.tech/imersi?sslmode=require', $connection['url']);
        $this->assertSame('ep-example.ap-southeast-1.aws.neon.tech', $connection['host']);
    }

    public function test_migrate_without_a_direct_url_drops_the_pooled_url(): void
    {
        $pooled = $this->pooledConnection();
        unset($pooled['direct']['url']);

        $connection = NeonMigrationConnection::forConsole($pooled, 'migrate:fresh');

        $this->assertNull($connection['url']);
        $this->assertSame('ep-example.ap-southeast-1.aws.neon.tech', $connection['host']);
    }

    public function test_application_requests_keep_the_pooled_url(): void
    {
        $pooled = $this->pooledConnection();

        $connection = NeonMigrationConnection::forConsole($pooled, 'serve');

        $this->assertSame($pooled['url'], $connection['url']);
        $this->assertSame($pooled['host'], $connection['host']);
    }

    public function test_schema_command_without_a_direct_host_keeps_the_pooler(): void
    {
        $pooled = $this->pooledConnection();
        $pooled['direct'] = [];

        $connection = NeonMigrationConnection::forConsole($pooled, 'db:seed');

        $this->assertSame($pooled['url'], $connection['url']);
    }

    /**
     * @return array<string, mixed>
     */
    private function pooledConnection(): array
    {
        return [
            'url' => 'postgresql://imersi:secret@ep-example-pooler.ap-southeast-1.aws.neon.tech/imersi?sslmode=require',
            'host' => 'ep-example-pooler.ap-southeast-1.aws.neon.tech',
            'direct' => [
                'url' => 'postgresql://imersi:secret@ep-example.ap-southeast-1.aws.neon.tech/imersi?sslmode=require',
                'host' => 'ep-example.ap-southeast-1.aws.neon.tech',
                'sslmode' => 'require',
            ],
        ];
    }
}
