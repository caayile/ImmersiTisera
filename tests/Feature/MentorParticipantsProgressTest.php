<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentorParticipantsProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_progress_only_shows_for_active_programs(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $programs = $mentor->mentor->programs()->with('participant.user')->get();
        $active = $programs->firstWhere('status', 'active') ?? $programs->first();
        $inactive = $programs->firstWhere('id', '!=', $active->id) ?? $programs->first();

        $active->update(['status' => 'active', 'progress' => 42]);
        $inactive->update(['status' => 'revision', 'progress' => 10]);

        $this->actingAs($mentor)
            ->get(route('mentor.participants'))
            ->assertOk()
            ->assertSee('42%')
            ->assertDontSee('10%');
    }
}
