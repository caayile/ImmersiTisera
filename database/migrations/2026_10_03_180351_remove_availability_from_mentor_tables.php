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
        if (Schema::hasColumn('mentors', 'availability')) {
            Schema::table('mentors', function (Blueprint $table) {
                $table->dropColumn('availability');
            });
        }
        if (Schema::hasTable('mentor_profiles') && Schema::hasColumn('mentor_profiles', 'availability')) {
            Schema::table('mentor_profiles', function (Blueprint $table) {
                $table->dropColumn('availability');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mentors') && ! Schema::hasColumn('mentors', 'availability')) {
            Schema::table('mentors', function (Blueprint $table) {
                $table->string('availability')->nullable();
            });
        }
        if (Schema::hasTable('mentor_profiles') && ! Schema::hasColumn('mentor_profiles', 'availability')) {
            Schema::table('mentor_profiles', function (Blueprint $table) {
                $table->string('availability')->nullable();
            });
        }
    }
};
