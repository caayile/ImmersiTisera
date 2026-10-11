<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\EvaluationQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEvaluationRecapTest extends TestCase
{
    use RefreshDatabase;

    private function fillQuestionnaire(User $dosen, int $unitScore = 2, int $otherScore = 5): void
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
                        'score' => $item['section'] === 'unit' ? $unitScore : $otherScore,
                    ])
                    ->all(),
            ])
            ->assertRedirect();
    }

    public function test_admin_evaluations_shows_department_recap_with_lowest_aspect(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->fillQuestionnaire($dosen);

        $departmentName = $dosen->participant->programs()->latest()->firstOrFail()->department->name;

        $this->actingAs($admin)
            ->get(route('admin.evaluations'))
            ->assertOk()
            ->assertSee('Rekap per departemen')
            ->assertSee($departmentName)
            ->assertSee('Aspek terendah')
            ->assertSee('Unit Bisnis memberikan akses data')
            ->assertSee($dosen->name);
    }

    public function test_admin_evaluations_recap_filters_by_department(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->fillQuestionnaire($dosen);

        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $other = Department::where('id', '!=', $program->department_id)->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.evaluations', ['department_id' => $other->id]))
            ->assertOk()
            ->assertDontSee($dosen->name)
            ->assertSee('Belum ada kuesioner peserta yang terisi.');
    }
}
