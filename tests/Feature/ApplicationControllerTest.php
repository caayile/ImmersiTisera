<?php

namespace Tests\Feature;

use App\Models\BusinessUnit;
use App\Models\Participant;
use App\Models\User;
use App\Notifications\ImersiAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_401_when_unauthenticated(): void
    {
        $this->postJson('/api/applications', [])->assertUnauthorized();
    }

    public function test_valid_payload_creates_application_and_returns_201(): void
    {
        $this->seed();
        Notification::fake();

        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($dosen)
            ->postJson('/api/applications', [
                'opportunity_id' => $unit->id,
                'primary_activity' => 'riset',
                'supporting_activity' => 'observasi',
                'proposed_shared_goal' => 'Menyusun teaching case dari proses digital bisnis.',
            ])
            ->assertCreated()
            ->assertJsonPath('status', 'submitted')
            ->assertJsonPath('opportunity.title', 'Digital Business');

        $this->assertDatabaseHas('applications', [
            'participant_id' => $dosen->participant->id,
            'business_unit_id' => $unit->id,
            'status' => 'submitted',
            'planned_activities' => 'riset',
        ]);

        Notification::assertSentTo(
            User::where('email', 'admin@imersi.id')->firstOrFail(),
            ImersiAlert::class,
        );
    }

    public function test_returns_422_when_dosen_profile_is_incomplete(): void
    {
        $this->seed();

        $user = User::factory()->create(['role' => 'participant']);
        Participant::create(['user_id' => $user->id]);
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($user)
            ->postJson('/api/applications', [
                'opportunity_id' => $unit->id,
                'primary_activity' => 'riset',
                'proposed_shared_goal' => 'Belajar industri.',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Lengkapi profil terlebih dahulu.');
    }

    public function test_returns_422_when_required_application_fields_are_missing(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'dosen@imersi.id')->firstOrFail())
            ->postJson('/api/applications', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['opportunity_id', 'primary_activity', 'proposed_shared_goal']);
    }

    public function test_forbids_mentor_from_creating_application(): void
    {
        $this->seed();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs(User::where('email', 'mentor@imersi.id')->firstOrFail())
            ->postJson('/api/applications', [
                'opportunity_id' => $unit->id,
                'primary_activity' => 'riset',
                'proposed_shared_goal' => 'Tidak boleh.',
            ])
            ->assertForbidden();
    }

    public function test_dosen_does_not_receive_another_dosen_application(): void
    {
        $this->seed();
        Notification::fake();

        $owner = $this->newDosen();
        $other = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($owner)
            ->postJson('/api/applications', [
                'opportunity_id' => $unit->id,
                'primary_activity' => 'riset',
                'proposed_shared_goal' => 'Usulan tujuan bersama.',
            ])
            ->assertCreated();

        $this->actingAs($other)
            ->getJson('/api/applications')
            ->assertOk()
            ->assertExactJson([]);
    }

    private function newDosen(): User
    {
        $user = User::factory()->create([
            'role' => 'participant',
            'status' => 'active',
            'verification_status' => 'verified',
        ]);

        Participant::create([
            'user_id' => $user->id,
            'nidn' => '0011223344',
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Informatika',
            'expertise' => ['AI'],
            'competency' => ['Analytics'],
            'motivation' => 'Memahami praktik industri secara langsung.',
        ]);

        return $user->fresh('participant');
    }
}
