<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Program;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineCheckpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_sees_own_weeks_and_updates_one(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $item = $program->timelines()->orderBy('week')->firstOrFail();

        $this->actingAs($dosen)
            ->get(route('participant.timeline'))
            ->assertOk()
            ->assertSee('Minggu '.$item->week)
            ->assertSee('Ubah rencana minggu ini');

        $this->actingAs($dosen)
            ->post(route('participant.timeline.update', $item), [
                'title' => 'Rencana revisi minggu '.$item->week,
                'description' => 'Fokus observasi lapangan.',
                'expected_output' => 'Catatan observasi.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('timelines', [
            'id' => $item->id,
            'title' => 'Rencana revisi minggu '.$item->week,
            'status' => 'submitted',
        ]);
    }

    public function test_participant_cannot_update_another_participant_timeline(): void
    {
        $this->seed();

        $item = Timeline::firstOrFail();

        $this->actingAs(User::where('email', 'dosen2@imersi.id')->firstOrFail())
            ->post(route('participant.timeline.update', $item), [
                'title' => 'Ubah paksa',
            ])
            ->assertForbidden();
    }

    public function test_mentor_approves_timeline_to_done(): void
    {
        $this->seed();

        $application = Application::whereHas('participant.user', fn ($query) => $query->where('email', 'dosen@imersi.id'))->firstOrFail();
        $item = Timeline::where('program_id', $application->program->id)->orderBy('week')->firstOrFail();
        $item->update(['status' => 'submitted']);

        $this->actingAs(User::where('email', 'mentor@imersi.id')->firstOrFail())
            ->post(route('mentor.timeline.review', $item), ['status' => 'done'])
            ->assertRedirect();

        $this->assertDatabaseHas('timelines', ['id' => $item->id, 'status' => 'done']);
    }

    public function test_mentor_revision_requires_note(): void
    {
        $this->seed();

        $item = Timeline::firstOrFail();
        $item->update(['status' => 'submitted']);

        $this->actingAs(User::where('email', 'mentor@imersi.id')->firstOrFail())
            ->post(route('mentor.timeline.review', $item), ['status' => 'pending'])
            ->assertSessionHasErrors('mentor_note');

        $this->actingAs(User::where('email', 'mentor@imersi.id')->firstOrFail())
            ->post(route('mentor.timeline.review', $item), [
                'status' => 'pending',
                'mentor_note' => 'Perjelas target hasilnya.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('timelines', [
            'id' => $item->id,
            'status' => 'pending',
            'mentor_note' => 'Perjelas target hasilnya.',
        ]);
    }

    public function test_mentor_timeline_list_links_to_detail_page(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $program = Program::whereHas('participant.user', fn ($query) => $query->where('name', 'Dr. Andi Pratama'))->firstOrFail();

        $this->actingAs($mentor)
            ->get(route('mentor.timeline'))
            ->assertOk()
            ->assertSee('Dr. Andi Pratama')
            ->assertSee('Lihat rincian');

        $this->actingAs($mentor)
            ->get(route('mentor.timeline.show', $program))
            ->assertOk()
            ->assertSee('Minggu 1')
            ->assertSee('Minta revisi');
    }

    public function test_other_mentor_cannot_view_timeline_detail(): void
    {
        $this->seed();

        $program = Program::firstOrFail();

        $this->actingAs(User::where('email', 'mentor-it@imersi.id')->firstOrFail())
            ->get(route('mentor.timeline.show', $program))
            ->assertForbidden();
    }

    public function test_other_mentor_cannot_review_timeline(): void
    {
        $this->seed();

        $item = Timeline::firstOrFail();

        $this->actingAs(User::where('email', 'mentor-it@imersi.id')->firstOrFail())
            ->post(route('mentor.timeline.review', $item), ['status' => 'done'])
            ->assertForbidden();
    }
}
