<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgramApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_participant_cannot_approve_their_own_checkpoint_through_the_api(): void
    {
        $this->seed();

        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = Program::where('participant_id', $participant->participant->id)->firstOrFail();
        $checkpoint = $program->timelines()->where('week', 2)->firstOrFail();

        $this->actingAs($participant)
            ->postJson('/api/programs/'.$program->id.'/checkpoints', [
                'week' => 2,
                'dosen_progress' => 'Checkpoint sudah saya kerjakan.',
                'mentor_status' => 'agree',
                'mentor_notes' => 'Disetujui.',
            ])
            ->assertUnprocessable();

        $this->assertDatabaseMissing('timelines', [
            'id' => $checkpoint->id,
            'status' => 'done',
        ]);
    }

    public function test_assigned_mentor_can_approve_a_checkpoint_through_the_api(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $program = Program::where('mentor_id', $mentor->mentor->id)->firstOrFail();
        $checkpoint = $program->timelines()->where('week', 2)->firstOrFail();

        $this->actingAs($mentor)
            ->postJson('/api/programs/'.$program->id.'/checkpoints', [
                'week' => 2,
                'mentor_status' => 'agree',
                'mentor_notes' => 'Checkpoint telah ditinjau.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('timelines', [
            'id' => $checkpoint->id,
            'status' => 'done',
            'expected_output' => 'Checkpoint telah ditinjau.',
        ]);
    }

    public function test_participant_cannot_write_mentor_feedback_through_the_api(): void
    {
        $this->seed();

        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = Program::where('participant_id', $participant->participant->id)->firstOrFail();

        $this->actingAs($participant)
            ->postJson('/api/programs/'.$program->id.'/mentorings', [
                'week' => 2,
                'mentor_feedback' => 'Sudah disetujui mentor.',
            ])
            ->assertUnprocessable();
    }
}
