<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\Mentor;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgreementActivatesProgramUsingApplicationPeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentor_agreement_approval_uses_application_period_not_approval_day(): void
    {
        $this->seed();

        $dosen = User::factory()->create([
            'role' => 'participant',
            'status' => 'active',
            'verification_status' => 'verified',
        ]);
        Participant::create([
            'user_id' => $dosen->id,
            'nidn' => '0011223399',
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Informatika',
        ]);
        $dosen = $dosen->fresh('participant');

        $mentor = Mentor::whereHas('user', fn ($query) => $query->where('email', 'mentor@imersi.id'))->firstOrFail();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $application = Application::create([
            'participant_id' => $dosen->participant->id,
            'department_id' => $unit->department_id,
            'business_unit_id' => $unit->id,
            'mentor_id' => $mentor->id,
            'status' => 'approved',
            'shared_goal' => 'Menyusun teaching case bersama mentor industri untuk kelas digital.',
            'problem_statement' => 'Perlu studi kasus workflow digital yang relevan.',
            'main_output' => 'Teaching case tervalidasi.',
            'participant_benefit' => 'Bahan ajar berbasis praktik industri.',
            'business_benefit' => 'Insight untuk unit bisnis mitra.',
            'period_start' => '2026-09-10',
            'period_end' => '2026-11-10',
        ]);

        $program = Program::create([
            'application_id' => $application->id,
            'participant_id' => $dosen->participant->id,
            'mentor_id' => $mentor->id,
            'department_id' => $unit->department_id,
            'business_unit_id' => $unit->id,
            'status' => 'submitted',
        ]);

        $agreement = Agreement::create([
            'program_id' => $program->id,
            'status' => 'submitted',
            'objective' => $application->shared_goal,
            'problem_statement' => $application->problem_statement,
            'main_output' => $application->main_output,
            'participant_signature' => $this->sampleSignature(),
            'participant_approved_at' => now(),
        ]);

        $this->actingAs($mentor->user)
            ->post(route('mentor.agreements.review', $agreement), [
                'decision' => 'agreed',
                'mentor_signature' => $this->sampleSignature(),
            ])
            ->assertRedirect();

        $program->refresh();

        $this->assertSame('active', $program->status);
        $this->assertSame('2026-09-10', $program->start_date->toDateString());
        $this->assertSame('2026-11-10', $program->end_date->toDateString());
        $this->assertNotSame(now()->toDateString(), $program->start_date->toDateString());
    }

    private function sampleSignature(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
    }
}
