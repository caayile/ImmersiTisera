<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\Department;
use App\Models\Mentor;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantNextStepTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_home_does_not_show_next_step(): void
    {
        $this->get(route('home'))
            ->assertDontSee('Langkah berikutnya');
    }

    public function test_incomplete_dosen_profile_prompts_to_complete_profile(): void
    {
        $dosen = $this->dosen();

        $this->actingAs($dosen)
            ->get(route('home'))
            ->assertSee('Lengkapi profil dosen')
            ->assertSee('Lengkapi profil')
            ->assertSee(route('participant.profile'), false);
    }

    public function test_complete_dosen_profile_prompts_to_browse_units(): void
    {
        $dosen = $this->dosen([
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Informatika',
        ]);

        $this->actingAs($dosen)
            ->get(route('home'))
            ->assertSee('Daftar ke unit bisnis')
            ->assertSee('Jelajahi unit bisnis')
            ->assertSee(route('departments.index'), false);
    }

    public function test_submitted_application_prompts_to_review_status(): void
    {
        $dosen = $this->dosen([
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Informatika',
        ]);
        $application = $this->application($dosen, 'submitted');

        $this->actingAs($dosen)
            ->get(route('home'))
            ->assertSee('Pendaftaran sedang ditinjau')
            ->assertSee('Menunggu tinjauan admin')
            ->assertSee(route('participant.applications.show', $application), false);
    }

    public function test_revision_application_prompts_to_edit_form(): void
    {
        $dosen = $this->dosen([
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Informatika',
        ]);
        $application = $this->application($dosen, 'revision');

        $this->actingAs($dosen)
            ->get(route('home'))
            ->assertSee('Perbaiki pendaftaran')
            ->assertSee('Perbaiki form')
            ->assertSee(route('participant.applications.edit', $application), false);
    }

    public function test_active_program_prompts_to_open_logbook(): void
    {
        $dosen = $this->dosen([
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Informatika',
        ]);
        $this->program($dosen, 'active');

        $this->actingAs($dosen)
            ->get(route('home'))
            ->assertSee('Isi logbook minggu ini')
            ->assertSee('Buka logbook')
            ->assertSee(route('participant.logbooks'), false);
    }

    public function test_draft_program_prompts_to_open_agreement(): void
    {
        $dosen = $this->dosen([
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Informatika',
        ]);
        $this->program($dosen, 'draft');

        $this->actingAs($dosen)
            ->get(route('home'))
            ->assertSee('Lanjutkan perjanjian imersi')
            ->assertSee('Buka perjanjian')
            ->assertSee(route('participant.agreement'), false);
    }

    public function test_completed_program_hides_next_step(): void
    {
        $dosen = $this->dosen([
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Informatika',
        ]);
        $this->program($dosen, 'completed');

        $this->actingAs($dosen)
            ->get(route('home'))
            ->assertDontSee('Langkah berikutnya');
    }

    public function test_mentor_home_does_not_show_dosen_next_step(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor']);

        $this->actingAs($mentor)
            ->get(route('home'))
            ->assertDontSee('Langkah berikutnya');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function dosen(array $attributes = []): User
    {
        $dosen = User::factory()->create(['role' => 'participant']);
        Participant::create(['user_id' => $dosen->id, ...$attributes]);

        return $dosen->fresh('participant');
    }

    private function application(User $dosen, string $status): Application
    {
        [$department, $unit] = $this->openUnit();

        return Application::create([
            'participant_id' => $dosen->participant->id,
            'department_id' => $department->id,
            'business_unit_id' => $unit->id,
            'status' => $status,
        ]);
    }

    private function program(User $dosen, string $status): Program
    {
        [$department, $unit] = $this->openUnit();
        $mentor = Mentor::create([
            'user_id' => User::factory()->create(['role' => 'mentor'])->id,
            'department_id' => $department->id,
            'business_unit_id' => $unit->id,
        ]);

        return Program::create([
            'participant_id' => $dosen->participant->id,
            'mentor_id' => $mentor->id,
            'department_id' => $department->id,
            'business_unit_id' => $unit->id,
            'status' => $status,
        ]);
    }

    /**
     * @return array{0: Department, 1: BusinessUnit}
     */
    private function openUnit(): array
    {
        $department = Department::create([
            'name' => 'TSPM',
            'slug' => 'tspm-next-step',
            'status' => 'active',
        ]);
        $unit = BusinessUnit::create([
            'department_id' => $department->id,
            'name' => 'Digital Business',
            'status' => 'open',
        ]);

        return [$department, $unit];
    }
}
