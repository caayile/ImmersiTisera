<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Explicit catalog metadata entered on the final-report form.
     * Plain integer columns (no FK constraints) to stay compatible
     * with SQLite/PostgreSQL/MySQL without doctrine/dbal.
     */
    public function up(): void
    {
        Schema::table('outputs', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('program_id');
            $table->unsignedBigInteger('business_unit_id')->nullable()->after('department_id');
            $table->string('year', 4)->nullable()->after('business_unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('outputs', function (Blueprint $table) {
            $table->dropColumn(['department_id', 'business_unit_id', 'year']);
        });
    }
};
