<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantBackLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_has_no_back_link(): void
    {
        $dosen = User::factory()->create(['role' => 'participant']);

        $this->actingAs($dosen)
            ->get(route('profile.public'))
            ->assertDontSee('Kembali');
    }

    public function test_registration_history_page_has_no_back_link(): void
    {
        $dosen = User::factory()->create(['role' => 'participant']);

        $this->actingAs($dosen)
            ->get(route('participant.applications'))
            ->assertDontSee('Kembali');
    }

    public function test_logbook_page_has_no_back_link(): void
    {
        $dosen = User::factory()->create(['role' => 'participant']);

        $this->actingAs($dosen)
            ->get(route('participant.logbooks'))
            ->assertOk()
            ->assertDontSee('Kembali')
            ->assertSee('Logbook');
    }
}
