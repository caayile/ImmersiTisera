<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantGradeVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_hidden_until_mentor_publishes_report_grades(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();

        Certificate::query()->updateOrCreate(
            ['program_id' => $program->id],
            [
                'status' => 'issued',
                'number' => 'CERT/TEST/001',
                'issued_at' => now(),
            ]
        );

        $this->actingAs($dosen)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee('Hasil Nilai Magang')
            ->assertSee('Nilai magang')
            ->assertDontSee('CERT/TEST/001');

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        Evaluation::create([
            'program_id' => $program->id,
            'evaluator_id' => $mentor->id,
            'grade_groups' => Evaluation::defaultGradeGroups(),
            'comments' => 'Raport siap.',
        ]);

        $this->actingAs($dosen)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee('Hasil Nilai Magang')
            ->assertSee('Nilai magang')
            ->assertDontSee('CERT/TEST/001');
    }

    public function test_completed_program_still_shows_grade_card(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $program->update(['status' => 'completed']);

        $this->actingAs($dosen)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee('Magang terakhir');
    }
}
