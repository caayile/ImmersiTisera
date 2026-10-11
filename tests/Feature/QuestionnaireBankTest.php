<?php

namespace Tests\Feature;

use App\Models\Evaluation;
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

        // Simpan: ubah kode + judul + pertanyaan + tambah baru.
        $this->actingAs($admin)
            ->post(route('admin.questionnaires.save', $section), [
                'key' => 'program',
                'title' => 'Evaluasi Program (diperbarui)',
                'questions' => [
                    ['id' => $first->id, 'question' => 'Pertanyaan pertama (diperbarui).', 'is_active' => '1'],
                ],
                'new_question' => 'Pertanyaan tambahan dari admin.',
                'save' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('evaluation_sections', ['id' => $section->id, 'title' => 'Evaluasi Program (diperbarui)']);
        $this->assertDatabaseHas('evaluation_questions', ['id' => $first->id, 'question' => 'Pertanyaan pertama (diperbarui).']);
        $this->assertDatabaseHas('evaluation_questions', [
            'evaluation_section_id' => $section->id,
            'question' => 'Pertanyaan tambahan dari admin.',
        ]);

        // Hapus satu pertanyaan dari form yang sama.
        $added = $section->questions()->where('question', 'Pertanyaan tambahan dari admin.')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.questionnaires.save', $section), ['delete_id' => $added->id])
            ->assertRedirect();

        $this->assertDatabaseMissing('evaluation_questions', ['id' => $added->id]);
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
