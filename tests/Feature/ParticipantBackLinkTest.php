<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantBackLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_has_a_back_link_to_home(): void
    {
        $dosen = User::factory()->create(['role' => 'participant']);

        $this->actingAs($dosen)
            ->get(route('profile.public'))
            ->assertSee('Kembali')
            ->assertSee(route('home'), false);
    }

    public function test_registration_history_page_has_a_back_link_to_home(): void
    {
        $dosen = User::factory()->create(['role' => 'participant']);

        $this->actingAs($dosen)
            ->get(route('participant.applications'))
            ->assertSee('Kembali')
            ->assertSee(route('home'), false);
    }

    public function test_logbook_page_has_a_back_link_to_home(): void
    {
        $dosen = User::factory()->create(['role' => 'participant']);

        $this->actingAs($dosen)
            ->get(route('participant.logbooks'))
            ->assertSee('Kembali')
            ->assertSee(route('home'), false);
    }
}
