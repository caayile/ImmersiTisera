<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $old = [
            'outputs', 'reports', 'evaluations', 'checkpoints', 'mentorings', 'logbooks',
            'programs', 'agreements', 'applications', 'opportunities', 'department_needs',
            'mentor_profiles', 'dosen_profiles',
        ];
        foreach ($old as $table) {
            Schema::dropIfExists($table);
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('role');
            }
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('function')->nullable();
            $table->string('area')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('business_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('function')->nullable();
            $table->text('work_done')->nullable();
            $table->text('example_activities')->nullable();
            $table->text('requirements')->nullable();
            $table->json('relevant_programs')->nullable();
            $table->string('period')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nidn')->nullable();
            $table->string('study_program')->nullable();
            $table->json('expertise')->nullable();
            $table->json('competency')->nullable();
            $table->text('experience')->nullable();
            $table->text('motivation')->nullable();
            $table->json('profile_data')->nullable();
            $table->timestamps();
        });

        Schema::create('mentors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('position')->nullable();
            $table->json('expertise')->nullable();
            $table->string('availability')->nullable();
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentor_id')->nullable()->constrained()->nullOnDelete();
            $table->text('motivation')->nullable();
            $table->string('preferred_period')->nullable();
            $table->unsignedTinyInteger('match_score')->default(0);
            $table->boolean('relevance_warning')->default(false);
            $table->text('matching_notes')->nullable();
            $table->string('status')->default('submitted');
            $table->timestamps();
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_unit_id')->constrained()->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->unsignedTinyInteger('current_week')->default(1);
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->text('objective')->nullable();
            $table->text('problem_statement')->nullable();
            $table->text('activities')->nullable();
            $table->string('main_output')->nullable();
            $table->text('participant_benefit')->nullable();
            $table->text('business_benefit')->nullable();
            $table->json('success_indicators')->nullable();
            $table->text('collaboration_potential')->nullable();
            $table->text('revision_note')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('participant_approved_at')->nullable();
            $table->timestamp('mentor_approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week');
            $table->string('phase');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('expected_output')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('activity')->nullable();
            $table->text('what_i_did')->nullable();
            $table->text('what_i_learned')->nullable();
            $table->text('what_i_found')->nullable();
            $table->text('value')->nullable();
            $table->text('next_action')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('mentor_feedback')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('mentor_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mentor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week');
            $table->date('session_date')->nullable();
            $table->text('findings')->nullable();
            $table->text('current_work')->nullable();
            $table->text('next_action')->nullable();
            $table->text('feedback')->nullable();
            $table->string('checkpoint_status')->nullable();
            $table->timestamps();
        });

        Schema::create('outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_main_output')->default(false);
            $table->boolean('is_final_report')->default(false);
            $table->text('mentor_feedback')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('industry_understanding')->default(0);
            $table->unsignedTinyInteger('relationship')->default(0);
            $table->unsignedTinyInteger('output')->default(0);
            $table->unsignedTinyInteger('mutual_benefit')->default(0);
            $table->unsignedTinyInteger('collaboration_potential')->default(0);
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::create('collaboration_pipelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('level')->default(0);
            $table->string('collaboration_type')->nullable();
            $table->text('description')->nullable();
            $table->text('next_action')->nullable();
            $table->string('responsible_person')->nullable();
            $table->date('target_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('collaboration_pipelines');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('outputs');
        Schema::dropIfExists('mentor_sessions');
        Schema::dropIfExists('logbooks');
        Schema::dropIfExists('timelines');
        Schema::dropIfExists('agreements');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('mentors');
        Schema::dropIfExists('participants');
        Schema::dropIfExists('business_units');
        Schema::dropIfExists('departments');
    }
};
