<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Models\BusinessUnit;
use App\Models\Mentor;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgreementPdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_download_agreed_agreement_pdf(): void
    {
        $this->seed();
        $mentor = Mentor::whereHas('user', fn ($q) => $q->where('email', 'mentor@imersi.id'))->firstOrFail();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $user = \App\Models\User::factory()->create(['role' => 'participant']);
        $participant = \App\Models\Participant::create([
            'user_id' => $user->id,
            'nidn' => '123',
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Informatika',
        ]);

        $sig = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $program = Program::create([
            'participant_id' => $participant->id,
            'mentor_id' => $mentor->id,
            'department_id' => $unit->department_id,
            'business_unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
        ]);
        $agreement = Agreement::create([
            'program_id' => $program->id,
            'objective' => 'Tujuan',
            'activities' => 'Aktivitas',
            'problem_statement' => 'Masalah',
            'main_output' => 'Output',
            'participant_benefit' => 'Manfaat dosen',
            'business_benefit' => 'Manfaat unit',
            'success_indicators' => ['Indikator satu yang panjang'],
            'status' => 'agreed',
            'letter_number' => '001/MD/TSU/TS/09-2026',
            'letter_issued_at' => now(),
            'participant_approved_at' => now(),
            'mentor_approved_at' => now(),
            'participant_signature' => $sig,
            'mentor_signature' => $sig,
        ]);

        $this->actingAs($user)
            ->get(route('participant.agreement.download'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
