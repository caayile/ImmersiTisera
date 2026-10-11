<?php

namespace Tests\Feature;

use App\Models\Evaluation;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportGradeTest extends TestCase
{
    use RefreshDatabase;

    private function reportGroups(): array
    {
        return [
            [
                'name' => 'Project',
                'weight' => 40,
                'aspects' => [
                    ['label' => 'Kualitas Hasil Kerja', 'score' => 80],
                    ['label' => 'Ketepatan Waktu', 'score' => 90],
                ],
            ],
            [
                'name' => 'Sikap',
                'weight' => 60,
                'aspects' => [
                    ['label' => 'Kehadiran', 'score' => 100],
                    ['label' => 'Kedisiplinan', 'score' => 90],
                ],
            ],
        ];
    }

    public function test_mentor_grade_list_shows_ungraded_status(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $program = Program::whereHas('mentor.user', fn ($query) => $query->whereKey($mentor->id))->firstOrFail();

        $this->actingAs($mentor)
            ->get(route('mentor.penilaian'))
            ->assertOk()
            ->assertSee('Belum dinilai')
            ->assertSee('Penilaian');

        $this->actingAs($mentor)
            ->get(route('mentor.penilaian.show', $program))
            ->assertOk()
            ->assertSee('Input Nilai Magang')
            ->assertSee('Tambah aspek');
    }

    public function test_predicate_boundaries(): void
    {
        foreach ([[100, 'A'], [85, 'A'], [84.9, 'B'], [70, 'B'], [69.9, 'C'], [60, 'C'], [59.9, 'D'], [50, 'D'], [49.9, 'E'], [0, 'E']] as [$score, $expected]) {
            $evaluation = new Evaluation([
                'grade_groups' => [['name' => 'Project', 'weight' => 100, 'aspects' => [['label' => 'X', 'score' => $score]]]],
            ]);

            $this->assertSame($expected, $evaluation->predicate(), "score $score");
        }

        $empty = new Evaluation(['grade_groups' => []]);
        $this->assertNull($empty->reportAverage());
        $this->assertNull($empty->predicate());
        $this->assertFalse($empty->hasReport());
    }

    public function test_mentor_report_rejects_invalid_scores(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $program = Program::whereHas('mentor.user', fn ($query) => $query->whereKey($mentor->id))->firstOrFail();

        $groups = $this->reportGroups();
        $groups[0]['aspects'][0]['score'] = 101;

        $this->actingAs($mentor)
            ->post(route('mentor.evaluations.store', $program), ['groups' => $groups])
            ->assertSessionHasErrors('groups.0.aspects.0.score');

        $this->assertDatabaseMissing('evaluations', [
            'program_id' => $program->id,
            'evaluator_id' => $mentor->id,
        ]);
    }

    public function test_dosen_report_is_locked_until_own_evaluation_submitted(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();

        Evaluation::create([
            'program_id' => $program->id,
            'evaluator_id' => $mentor->id,
            'grade_groups' => $this->reportGroups(),
            'comments' => 'Bagus.',
        ]);

        // Terkunci: tidak boleh intip nilai sebelum mengisi form sendiri.
        $this->actingAs($dosen)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee('terkunci')
            ->assertDontSee('Bagus.');

        // Setelah mengisi evaluasi sendiri, raport terbuka.
        $postResponse = $this->actingAs($dosen)
            ->post(route('participant.evaluation'), [
                'criteria' => [['label' => 'Umpan Balik', 'score' => 4]],
                'comments' => 'Terima kasih.',
            ]);
        $postResponse->assertRedirect();
        $postResponse->assertSessionHasNoErrors();
        $this->assertDatabaseHas('evaluations', [
            'program_id' => $program->id,
            'evaluator_id' => $dosen->id,
        ]);

        $this->actingAs($dosen)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee('Lihat Detail')
            ->assertDontSee('terkunci');

        $this->actingAs($dosen)
            ->get(route('participant.evaluation.report'))
            ->assertOk()
            ->assertSee('Nilai raport dari')
            ->assertSee('Bagus.')
            ->assertSee('91');
    }

    public function test_legacy_mentor_evaluation_stays_visible_without_own_evaluation(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();

        Evaluation::create([
            'program_id' => $program->id,
            'evaluator_id' => $mentor->id,
            'criteria' => [['label' => 'Relasi', 'score' => 4]],
            'comments' => 'Baik.',
        ]);

        $this->actingAs($dosen)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee('Relasi')
            ->assertDontSee('terkunci');
    }

    public function test_report_pdf_is_locked_until_own_evaluation_submitted(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();

        Evaluation::create([
            'program_id' => $program->id,
            'evaluator_id' => $mentor->id,
            'grade_groups' => $this->reportGroups(),
            'comments' => 'Bagus.',
        ]);

        $this->actingAs($dosen)
            ->get(route('participant.evaluation.report-pdf'))
            ->assertNotFound();

        $this->actingAs($dosen)
            ->get(route('participant.evaluation.report-preview'))
            ->assertNotFound();
    }

    public function test_dosen_can_download_unlocked_report_pdf(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();

        Evaluation::create([
            'program_id' => $program->id,
            'evaluator_id' => $mentor->id,
            'grade_groups' => $this->reportGroups(),
            'comments' => 'Bagus.',
        ]);
        Evaluation::create([
            'program_id' => $program->id,
            'evaluator_id' => $dosen->id,
            'criteria' => [['label' => 'Umpan Balik', 'score' => 4]],
        ]);

        $response = $this->actingAs($dosen)->get(route('participant.evaluation.report-pdf'));

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());

        $this->actingAs($dosen)
            ->get(route('participant.evaluation.report-preview'))
            ->assertOk()
            ->assertSee('LAPORAN NILAI MAGANG DOSEN')
            ->assertSee('Cetak', false);
    }

    public function test_history_lists_past_cycles_and_archive_detail_opens(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $current = $dosen->participant->programs()->latest()->firstOrFail();

        $old = Program::create([
            'participant_id' => $current->participant_id,
            'mentor_id' => $current->mentor_id,
            'department_id' => $current->department_id,
            'business_unit_id' => $current->business_unit_id,
            'start_date' => '2024-10-01',
            'end_date' => '2024-12-01',
            'status' => 'completed',
        ]);
        $old->created_at = now()->subYears(2);
        $old->updated_at = now()->subYears(2);
        $old->save();

        Evaluation::create([
            'program_id' => $old->id,
            'evaluator_id' => $mentor->id,
            'grade_groups' => $this->reportGroups(),
            'comments' => 'Arsip dua tahun lalu.',
        ]);

        // Tanpa mengisi kuesioner siklus berjalan: arsip tetap terlihat.
        $this->actingAs($dosen)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee('Riwayat Hasil Magang Dosen')
            ->assertSee('Oct 2024')
            ->assertSee('2024');

        // Detail arsip terbuka tanpa kuesioner; rapor tertulis di sana.
        $this->actingAs($dosen)
            ->get(route('participant.evaluation.report', $old))
            ->assertOk()
            ->assertSee('Nilai raport dari')
            ->assertSee('Arsip dua tahun lalu.');
    }
}
