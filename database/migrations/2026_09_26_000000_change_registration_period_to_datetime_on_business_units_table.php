<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * registration_start/deadline were created as DATE columns, so any
     * time set by admin (e.g. 15:00) was truncated to 00:00 on
     * PostgreSQL/MySQL. Convert them to DATETIME without doctrine/dbal.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $this->alterPgsql('TIMESTAMP');
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->alterMysql('DATETIME');
        }
        // SQLite stores datetimes as text already, so no change is needed
        // there for time preservation; other drivers are left untouched.
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            $this->alterPgsql('DATE');
        } elseif (in_array($driver, ['mysql', 'mariadb'], true)) {
            $this->alterMysql('DATE');
        }
    }

    private function alterPgsql(string $type): void
    {
        foreach (['registration_start', 'registration_deadline'] as $column) {
            DB::statement("ALTER TABLE business_units ALTER COLUMN {$column} TYPE {$type} USING {$column}::timestamp");
        }
    }

    private function alterMysql(string $type): void
    {
        foreach (['registration_start', 'registration_deadline'] as $column) {
            DB::statement("ALTER TABLE business_units MODIFY {$column} {$type} NULL");
        }
    }
};
