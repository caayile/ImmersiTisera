<?php

namespace Tests\Feature;

use App\Models\Evaluation;
use App\Models\EvaluationQuestion;
use App\Models\EvaluationScale;
use App\Models\EvaluationSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestionnaireBankTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_questionnaire_bank_in_one_form(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $section = EvaluationSection::where('key', 'program')->firstOrFail();
        $first = $section->questions()->orderBy('position')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.questionnaires'))
            ->assertOk()
            ->assertSee('Bank Kuesioner Evaluasi')
            ->assertSee($section->title)
            ->assertSee('Skala Penilaian')
            ->assertSee('Sangat Baik');

        $this->actingAs($admin)
            ->get(route('admin.questionnaires.show', $section))
            ->assertOk()
            ->assertSee($section->title)
            ->assertSee($first->question);

        // Simpan: ubah kode + judul + pertanyaan + tambah baris baru.
        $this->actingAs($admin)
            ->post(route('admin.questionnaires.save', $section), [
                'key' => 'program',
                'title' => 'Evaluasi Program (diperbarui)',
                'questions' => [
                    ['id' => $first->id, 'question' => 'Pertanyaan pertama (diperbarui).'],
                    ['question' => 'Pertanyaan tambahan dari admin.'],
                ],
                'save' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('evaluation_sections', ['id' => $section->id, 'title' => 'Evaluasi Program (diperbarui)']);
        $this->assertDatabaseHas('evaluation_questions', ['id' => $first->id, 'question' => 'Pertanyaan pertama (diperbarui).']);
        $this->assertDatabaseHas('evaluation_questions', [
            'evaluation_section_id' => $section->id,
            'question' => 'Pertanyaan tambahan dari admin.',
        ]);

        // Hapus pertanyaan tambahan dengan menyimpan tanpa baris itu.
        $this->actingAs($admin)
            ->post(route('admin.questionnaires.save', $section), [
                'key' => 'program',
                'title' => 'Evaluasi Program (diperbarui)',
                'questions' => [
                    ['id' => $first->id, 'question' => 'Pertanyaan pertama (diperbarui).'],
                ],
                'save' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('evaluation_questions', [
            'evaluation_section_id' => $section->id,
            'question' => 'Pertanyaan tambahan dari admin.',
        ]);
    }

    public function test_default_bank_has_five_questions_per_section_and_departemen_key(): void
    {
        $this->seed();

        $bank = EvaluationQuestion::defaultBank();

        $this->assertCount(3, $bank);
        $this->assertSame(['program', 'departemen', 'mentor'], array_column($bank, 'key'));

        foreach ($bank as $section) {
            $this->assertCount(5, $section['questions']);
        }

        $this->assertDatabaseHas('evaluation_sections', ['key' => 'departemen', 'title' => 'Evaluasi terhadap Departemen']);
        $this->assertDatabaseCount('evaluation_questions', 15);
        $this->assertDatabaseMissing('evaluation_sections', ['key' => 'unit']);
    }

    public function test_admin_can_manage_likert_scales(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();

        // Tambah skala baru.
        $this->actingAs($admin)
            ->post(route('admin.scales.store'), ['label' => 'Istimewa', 'value' => 6])
            ->assertRedirect();

        $this->assertDatabaseHas('evaluation_scales', ['label' => 'Istimewa', 'value' => 6]);

        $scale = EvaluationScale::where('value', 6)->firstOrFail();

        // Ubah skala.
        $this->actingAs($admin)
            ->put(route('admin.scales.update', $scale), ['label' => 'Luar Biasa', 'value' => 6])
            ->assertRedirect();

        $this->assertDatabaseHas('evaluation_scales', ['id' => $scale->id, 'label' => 'Luar Biasa']);

        // Hapus skala.
        $this->actingAs($admin)
            ->delete(route('admin.scales.destroy', $scale))
            ->assertRedirect();

        $this->assertDatabaseMissing('evaluation_scales', ['id' => $scale->id]);
    }

    public function test_participant_quiz_follows_admin_bank(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();

        Evaluation::create([
            'program_id' => $program->id,
            'evaluator_id' => $mentor->id,
            'grade_groups' => Evaluation::defaultGradeGroups(),
            'comments' => 'Bagus.',
        ]);

        $bankQuestion = EvaluationSection::where('key', 'program')->firstOrFail()
            ->questions()->orderBy('position')->firstOrFail()->question;

        $this->actingAs($dosen)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee($bankQuestion)
            ->assertSee('Selanjutnya')
            ->assertSee('Kritik Dan Saran');
    }
}
