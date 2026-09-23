<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('shared_goal')->nullable()->after('campus_benefit');
            $table->json('activity_types')->nullable()->after('shared_goal');
            $table->text('problem_statement')->nullable()->after('activity_types');
            $table->text('main_output')->nullable()->after('problem_statement');
            $table->text('participant_benefit')->nullable()->after('main_output');
            $table->text('business_benefit')->nullable()->after('participant_benefit');
            $table->json('success_indicators')->nullable()->after('business_benefit');
            $table->text('indicator_feedback')->nullable()->after('success_indicators');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'shared_goal',
                'activity_types',
                'problem_statement',
                'main_output',
                'participant_benefit',
                'business_benefit',
                'success_indicators',
                'indicator_feedback',
            ]);
        });
    }
};
