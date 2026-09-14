<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Department;
use Tests\TestCase;

class DatabaseConnectionsTest extends TestCase
{
    public function test_postgresql_and_mysql_connections_stay_isolated_from_sqlite_tests(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        $this->assertSame('pgsql', config('database.connections.pgsql.driver'));
        $this->assertSame('5432', (string) config('database.connections.pgsql.port'));
        $this->assertSame('imersi', config('database.connections.pgsql.database'));
        $this->assertSame('postgres', config('database.connections.pgsql.username'));

        $this->assertSame('mysql', config('database.connections.mysql.driver'));
        $this->assertSame('3306', (string) config('database.connections.mysql.port'));
        $this->assertSame('imersi', config('database.connections.mysql.database'));
        $this->assertSame('root', config('database.connections.mysql.username'));

        $this->assertSame('pgsql', config('database.connections.neon.driver'));
        $this->assertSame('require', config('database.connections.neon.sslmode'));
        $this->assertFalse(config('database.connections.neon.pooled'));
        $this->assertSame([], config('database.connections.neon.direct'));
    }

    public function test_neon_pooled_url_derives_a_direct_host_for_migrations(): void
    {
        $config = neon_database_config('postgresql://imersi:secret@ep-example-pooler.ap-southeast-1.aws.neon.tech/imersi?sslmode=require');

        $this->assertTrue($config['pooled']);
        $this->assertSame('ep-example-pooler.ap-southeast-1.aws.neon.tech', $config['host']);
        $this->assertSame('ep-example.ap-southeast-1.aws.neon.tech', $config['direct']['host']);
        $this->assertSame('require', $config['sslmode']);
        $this->assertSame('require', $config['direct']['sslmode']);
        $this->assertSame('imersi', $config['database']);
        $this->assertSame('imersi', $config['username']);
    }

    public function test_json_columns_round_trip_on_the_default_connection(): void
    {
        $department = Department::create([
            'name' => 'Driver Check',
            'slug' => 'driver-check',
            'description' => 'Memastikan kolom JSON aman di PostgreSQL dan MySQL.',
            'status' => 'active',
        ]);

        $unit = BusinessUnit::create([
            'department_id' => $department->id,
            'name' => 'Analytics',
            'relevant_programs' => ['Informatika', 'Sistem Informasi'],
            'status' => 'open',
        ]);

        $this->assertSame(['Informatika', 'Sistem Informasi'], $unit->fresh()->relevant_programs);
        $this->assertDatabaseHas('business_units', [
            'id' => $unit->id,
            'department_id' => $department->id,
            'name' => 'Analytics',
        ]);
    }
}
