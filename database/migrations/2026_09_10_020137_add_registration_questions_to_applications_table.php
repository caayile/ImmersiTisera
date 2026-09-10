<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('learning_objectives')->nullable()->after('planned_activities');
            $table->text('expected_output')->nullable()->after('learning_objectives');
            $table->text('campus_benefit')->nullable()->after('expected_output');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['learning_objectives', 'expected_output', 'campus_benefit']);
        });
    }
};
