<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Evaluation;
use App\Models\Program;
use App\Models\ProgramOutput;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlowConsistencyFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dosen_user_menu_dashboard_points_to_program_dashboard(): void
    {
        $this->seed();
        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->actingAs($dosen)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Dashboard Program')
            ->assertSee(route('participant.dashboard'), false);
    }

    public function test_archive_report_preview_uses_opened_program_cycle(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $current = $dosen->participant->programs()->latest('id')->firstOrFail();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $archive = Program::create([
            'participant_id' => $dosen->participant->id,
            'mentor_id' => $current->mentor_id,
            'department_id' => $unit->department_id,
            'business_unit_id' => $unit->id,
            'status' => 'completed',
            'start_date' => now()->subMonths(6)->toDateString(),
            'end_date' => now()->subMonths(4)->toDateString(),
        ]);
        $archive->forceFill([
            'created_at' => now()->subYear(),
            'updated_at' => now()->subYear(),
        ])->save();

        Evaluation::create([
            'program_id' => $archive->id,
            'evaluator_id' => $mentor->id,
            'grade_groups' => [[
                'name' => 'Project',
                'weight' => 100,
                'aspects' => [['label' => 'Kualitas', 'score' => 90]],
            ]],
            'comments' => 'Raport arsip khusus.',
        ]);

        $this->assertSame($current->id, $dosen->participant->programs()->latest()->value('id'));

        $this->actingAs($dosen)
            ->get(route('participant.evaluation.report', $archive))
            ->assertOk()
            ->assertSee('Raport arsip khusus.');

        $this->actingAs($dosen)
            ->get(route('participant.evaluation.report-preview', $archive))
            ->assertOk()
            ->assertSee('Raport arsip khusus.');
    }

    public function test_program_cannot_complete_without_mentor_report(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest('id')->firstOrFail();
        $program->update(['status' => 'active']);

        ProgramOutput::create([
            'program_id' => $program->id,
            'participant_id' => $dosen->participant->id,
            'department_id' => $program->department_id,
            'business_unit_id' => $program->business_unit_id,
            'title' => 'Hasil utama',
            'type' => 'Dokumen',
            'status' => 'approved',
            'is_main_output' => true,
        ]);
        ProgramOutput::create([
            'program_id' => $program->id,
            'participant_id' => $dosen->participant->id,
            'department_id' => $program->department_id,
            'business_unit_id' => $program->business_unit_id,
            'title' => 'Laporan akhir',
            'type' => 'Dokumen',
            'status' => 'approved',
            'is_final_report' => true,
        ]);

        Evaluation::create([
            'program_id' => $program->id,
            'evaluator_id' => $dosen->id,
            'criteria' => [['label' => 'Umpan balik', 'score' => 4]],
        ]);

        $this->assertFalse($program->fresh()->canComplete());

        Evaluation::create([
            'program_id' => $program->id,
            'evaluator_id' => User::where('email', 'mentor@imersi.id')->value('id'),
            'grade_groups' => [[
                'name' => 'Project',
                'weight' => 100,
                'aspects' => [['label' => 'Kualitas', 'score' => 88]],
            ]],
        ]);

        $this->assertTrue($program->fresh()->canComplete());
    }

    public function test_evaluation_spreadsheet_headers_put_unit_bisnis_before_departemen(): void
    {
        $this->seed();
        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();

        $response = $this->actingAs($admin)
            ->get(route('admin.evaluations', ['export' => 'csv']))
            ->assertOk();

        $csv = $response->streamedContent();
        $header = strtok($csv, "\n");

        $this->assertMatchesRegularExpression('/Unit Bisnis.*,Departemen/', $header);
        $this->assertDoesNotMatchRegularExpression('/^.*,Departemen.*,Unit Bisnis,/', $header);
    }
}
