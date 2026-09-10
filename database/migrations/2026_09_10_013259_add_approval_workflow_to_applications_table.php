<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('planned_activities')->nullable()->after('motivation');
            $table->string('letter_number')->nullable()->after('matching_notes');
            $table->text('mentor_note')->nullable()->after('letter_number');
            $table->text('revision_note')->nullable()->after('mentor_note');
            $table->timestamp('admin_reviewed_at')->nullable()->after('revision_note');
            $table->timestamp('mentor_reviewed_at')->nullable()->after('admin_reviewed_at');
            $table->timestamp('admin_finalized_at')->nullable()->after('mentor_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'planned_activities',
                'letter_number',
                'mentor_note',
                'revision_note',
                'admin_reviewed_at',
                'mentor_reviewed_at',
                'admin_finalized_at',
            ]);
        });
    }
};
