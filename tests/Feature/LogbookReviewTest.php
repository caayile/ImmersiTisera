<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogbookReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentor_sees_participant_logbooks_with_business_unit_and_department_filter(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();

        $this->actingAs($mentor)
            ->get(route('mentor.logbooks', ['q' => 'Andi']))
            ->assertOk()
            ->assertSee('Dr. Andi Pratama')
            ->assertSee('Digital Business')
            ->assertSee('TSPM')
            ->assertDontSee('Dr. Maya Kusuma');

        $program = Program::whereHas('participant.user', fn ($query) => $query->where('name', 'Dr. Andi Pratama'))->firstOrFail();

        $this->actingAs($mentor)
            ->get(route('mentor.logbooks.show', $program))
            ->assertOk()
            ->assertSee('Orientation Digital Business')
            ->assertSee('Feedback / next action');
    }

    public function test_admin_can_filter_and_open_participant_logbooks(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.monitoring', ['status' => 'submitted']))
            ->assertOk()
            ->assertSee('Dr. Andi Pratama')
            ->assertSee('Semua unit bisnis');

        $program = Program::whereHas('participant.user', fn ($query) => $query->where('name', 'Dr. Andi Pratama'))->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.monitoring.show', $program))
            ->assertOk()
            ->assertSee('Orientation Digital Business')
            ->assertDontSee('Feedback / next action');
    }
}
