<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->unsignedInteger('global_sequence')->nullable()->unique()->after('number');
            $table->unsignedInteger('batch')->nullable()->after('global_sequence');
            $table->unsignedTinyInteger('issued_month')->nullable()->after('batch');
            $table->unsignedSmallInteger('issued_year')->nullable()->after('issued_month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['global_sequence', 'batch', 'issued_month', 'issued_year']);
        });
    }
};
