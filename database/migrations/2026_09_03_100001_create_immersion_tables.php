<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nidn')->nullable();
            $table->string('prodi')->nullable();
            $table->string('department')->nullable();
            $table->json('expertise')->nullable();
            $table->json('interests')->nullable();
            $table->text('experience')->nullable();
            $table->string('purpose')->nullable();
            $table->text('goals')->nullable();
            $table->text('competency_gap')->nullable();
            $table->timestamps();
        });

        Schema::create('mentor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('industry_field')->nullable();
            $table->string('business_unit')->nullable();
            $table->string('department_function')->nullable();
            $table->string('job_title')->nullable();
            $table->json('expertise')->nullable();
            $table->text('industry_needs')->nullable();
            $table->text('problems')->nullable();
            $table->text('opportunities')->nullable();
            $table->text('dosen_needs')->nullable();
            $table->string('availability')->nullable();
            $table->timestamps();
        });

        Schema::create('department_needs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('prodi');
            $table->string('department')->nullable();
            $table->string('purpose');
            $table->text('academic_needs')->nullable();
            $table->text('problem')->nullable();
            $table->text('goal')->nullable();
            $table->timestamps();
        });

        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('field')->nullable();
            $table->string('business_unit')->nullable();
            $table->string('purpose')->nullable();
            $table->text('problem')->nullable();
            $table->text('opportunity')->nullable();
            $table->json('needed_expertise')->nullable();
            $table->text('expected_output')->nullable();
            $table->json('allowed_activities')->nullable();
            $table->date('timeline_start')->nullable();
            $table->date('timeline_end')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('match_score')->default(0);
            $table->string('match_label')->nullable();
            $table->string('primary_activity');
            $table->string('supporting_activity')->nullable();
            $table->text('proposed_shared_goal')->nullable();
            $table->string('status')->default('pending');
            $table->text('mentor_note')->nullable();
            $table->timestamps();
        });

        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->string('prodi')->nullable();
            $table->string('business_unit')->nullable();
            $table->string('department_function')->nullable();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->text('shared_goal')->nullable();
            $table->text('problem_opportunity')->nullable();
            $table->string('primary_activity')->nullable();
            $table->string('supporting_activity')->nullable();
            $table->text('promised_output')->nullable();
            $table->text('benefit_dosen')->nullable();
            $table->text('benefit_industry')->nullable();
            $table->text('success_indicator')->nullable();
            $table->json('timeline')->nullable();
            $table->text('potential_collaboration')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('mentor_approved_at')->nullable();
            $table->timestamp('dosen_approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('opportunity_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            $table->string('current_phase')->default('discover');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('industry_insight')->nullable();
            $table->text('problem_statement')->nullable();
            $table->text('contribution_notes')->nullable();
            $table->decimal('collaboration_score', 3, 1)->nullable();
            $table->string('connect_decision')->nullable();
            $table->text('connect_notes')->nullable();
            $table->unsignedTinyInteger('maturity_level')->default(1);
            $table->timestamps();
        });

        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date');
            $table->text('what_did')->nullable();
            $table->text('what_learned')->nullable();
            $table->text('what_found')->nullable();
            $table->text('obstacles')->nullable();
            $table->text('output')->nullable();
            $table->boolean('mentor_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mentorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week');
            $table->date('session_date')->nullable();
            $table->text('found')->nullable();
            $table->text('working_on')->nullable();
            $table->text('next_action')->nullable();
            $table->text('mentor_feedback')->nullable();
            $table->timestamps();
        });

        Schema::create('checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('week');
            $table->text('dosen_progress')->nullable();
            $table->string('mentor_status')->nullable();
            $table->text('mentor_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->unsignedTinyInteger('industry_understanding')->default(0);
            $table->unsignedTinyInteger('relationship')->default(0);
            $table->unsignedTinyInteger('output_quality')->default(0);
            $table->unsignedTinyInteger('mutual_benefit')->default(0);
            $table->unsignedTinyInteger('collaboration_potential')->default(0);
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->json('content')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();
        });

        Schema::create('outputs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outputs');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('evaluations');
        Schema::dropIfExists('checkpoints');
        Schema::dropIfExists('mentorings');
        Schema::dropIfExists('logbooks');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('agreements');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('department_needs');
        Schema::dropIfExists('mentor_profiles');
        Schema::dropIfExists('dosen_profiles');
    }
};
