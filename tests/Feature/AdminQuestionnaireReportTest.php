<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\EvaluationQuestion;
use App\Models\Mentor;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQuestionnaireReportTest extends TestCase
{
    use RefreshDatabase;

    private function fillQuestionnaire(User $dosen, int $unitScore = 2, int $otherScore = 5, ?string $comments = null): void
    {
        $this->actingAs($dosen)
            ->post(route('participant.evaluation'), [
                'criteria' => collect(EvaluationQuestion::defaultBank())
                    ->flatMap(fn ($section) => collect($section['questions'])->map(fn ($question) => [
                        'label' => $question,
                        'section' => $section['key'],
                    ]))
                    ->map(fn ($item) => [
                        'label' => $item['label'],
                        'score' => $item['section'] === 'departemen' ? $unitScore : $otherScore,
                    ])
                    ->all(),
                'comments' => $comments,
            ])
            ->assertRedirect();
    }

    public function test_admin_sees_questionnaire_table_chart_and_section_scores(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $dosen->update(['name' => 'Andi <script>alert(1)</script>']);

        $this->fillQuestionnaire($dosen->fresh(), comments: 'Fasilitas perlu diperjelas.');

        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $unitName = $program->businessUnit->name;
        $mentorName = $program->mentor->user->name;
        $programLabel = 'Andi <script>alert(1)</script> — '.$unitName;

        $this->actingAs($admin)
            ->get(route('admin.reports'))
            ->assertOk()
            ->assertSee('Laporan kuesioner')
            ->assertSee('Pengisi kuesioner')
            ->assertSee('Rata-rata per bagian')
            ->assertSee('Rata-rata per pernyataan')
            ->assertSee('Pernyataan')
            ->assertSee('Per program')
            ->assertSee('Per departemen')
            ->assertSee('Per mentor')
            ->assertSee('questionnaireSectionChart')
            ->assertSee('Semua program')
            ->assertSee('Semua departemen')
            ->assertSee('Semua mentor')
            ->assertSee('Departemen memberikan akses data, fasilitas, dan informasi yang memadai.')
            ->assertSee('2.0 · Tidak Setuju')
            ->assertSee('5.0 · Sangat Setuju')
            ->assertSee('4.0 · Setuju')
            ->assertSee('Kritik dan saran')
            ->assertSee('Fasilitas perlu diperjelas.')
            ->assertSee('Ekspor kuesioner')
            ->assertSee('Andi <script>alert(1)</script>')
            ->assertSee('Dr. Bima Nugraha')
            ->assertSee('Pemahaman Industri')
            ->assertSee('Program sangat relevan untuk pembaruan kurikulum.')
            ->assertDontSee('Potensi guest lecture dan student project tinggi.')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertViewHas('questionnaire', function (array $report) use ($unitName, $mentorName, $programLabel): bool {
                $sections = collect($report['sections'])->keyBy('key');

                $this->assertSame(2, $report['respondents']);
                $this->assertSame(4.2, $report['overall']);
                $this->assertSame(5.0, $sections['program']['average']);
                $this->assertSame(2.0, $sections['departemen']['average']);
                $this->assertSame(5.0, $sections['mentor']['average']);
                $this->assertSame('Departemen memberikan akses data, fasilitas, dan informasi yang memadai.', $report['lowest']['label']);
                $this->assertSame(2.0, $report['lowest']['average']);
                $this->assertSame($programLabel, $report['by_program'][0]['label']);
                $this->assertSame(4.0, $report['by_program'][0]['average']);
                $departemen = collect($report['by_departemen'])->keyBy('label');
                $mentors = collect($report['by_mentor'])->keyBy('label');
                $this->assertSame(4.0, $departemen[$unitName]['average']);
                $this->assertSame(4.8, $departemen['Center Of Excellence']['average']);
                $this->assertSame(4.0, $mentors[$mentorName]['average']);
                $this->assertSame(4.8, $mentors['Agus Wijaya']['average']);
                $bima = collect($report['rows'])->firstWhere('participant', 'Dr. Bima Nugraha');
                $this->assertNotNull($bima);
                $this->assertSame('Program sangat relevan untuk pembaruan kurikulum.', $bima['comments']);
                $this->assertSame('Pemahaman Industri', $bima['answers'][0]['label']);
                $this->assertSame(5.0, $bima['answers'][0]['score']);
                $andi = collect($report['rows'])->first(fn (array $row): bool => str_contains($row['participant'], 'Andi'));
                $this->assertNotNull($andi);
                $this->assertSame(5.0, $andi['scores']['program']);
                $this->assertSame(2.0, $andi['scores']['departemen']);
                $this->assertSame(5.0, $andi['scores']['mentor']);
                $this->assertSame($unitName, $andi['departemen']);
                $this->assertSame('Fasilitas perlu diperjelas.', $andi['comments']);

                return true;
            });

        $this->actingAs($admin)
            ->get(route('admin.reports', [
                'program_id' => $program->id,
                'business_unit_id' => $program->business_unit_id,
                'mentor_id' => $program->mentor_id,
            ]))
            ->assertOk()
            ->assertViewHas('questionnaire', function (array $report): bool {
                $this->assertSame(1, $report['respondents']);

                return true;
            });

        $csv = $this->actingAs($admin)
            ->get(route('admin.reports', [
                'export' => 'kuesioner',
                'program_id' => $program->id,
                'business_unit_id' => $program->business_unit_id,
                'mentor_id' => $program->mentor_id,
            ]))
            ->assertOk();

        $content = $csv->streamedContent();
        $this->assertStringContainsString('Kritik dan Saran', $content);
        $this->assertStringContainsString('Fasilitas perlu diperjelas.', $content);
        $this->assertStringContainsString('Sangat Setuju', $content);
        $this->assertStringContainsString('Tidak Setuju', $content);
        $this->assertStringContainsString('laporan-kuesioner.csv', (string) $csv->headers->get('content-disposition'));
    }

    public function test_questionnaire_report_excludes_other_program_department_and_mentor(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->fillQuestionnaire($dosen, comments: 'Catatan yang tidak boleh ikut filter lain.');

        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $otherProgram = Program::query()->whereKeyNot($program->id)->firstOrFail();
        $otherUnit = BusinessUnit::query()->whereKeyNot($program->business_unit_id)->firstOrFail();
        $otherMentor = Mentor::query()->whereKeyNot($program->mentor_id)->firstOrFail();

        foreach ([
            ['program_id' => $otherProgram->id],
            ['business_unit_id' => $otherUnit->id],
            ['mentor_id' => $otherMentor->id],
        ] as $filter) {
            $this->actingAs($admin)
                ->get(route('admin.reports', $filter))
                ->assertOk()
                ->assertSee('Belum ada kuesioner peserta yang terisi.')
                ->assertViewHas('questionnaire', function (array $report): bool {
                    $this->assertSame(0, $report['respondents']);
                    $this->assertNull($report['overall']);
                    $this->assertSame([], $report['rows']);

                    return true;
                });
        }

        $csv = $this->actingAs($admin)
            ->get(route('admin.reports', [
                'export' => 'kuesioner',
                'program_id' => $otherProgram->id,
            ]))
            ->assertOk();

        $this->assertStringNotContainsString('Catatan yang tidak boleh ikut filter lain.', $csv->streamedContent());
    }

    public function test_guest_is_redirected_from_admin_reports(): void
    {
        $this->get(route('admin.reports'))
            ->assertRedirect(route('login'));
    }

    public function test_participant_cannot_open_admin_reports(): void
    {
        $dosen = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $this->actingAs($dosen)
            ->get(route('admin.reports'))
            ->assertForbidden();
    }
}
