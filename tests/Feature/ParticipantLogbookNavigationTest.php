<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantLogbookNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_applications_page_renders_logbook_cards(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->actingAs($dosen)
            ->get(route('participant.applications'))
            ->assertOk()
            ->assertSee('Logbook')
            ->assertSee('Riwayat Logbook')
            ->assertSee(route('participant.logbooks'))
            ->assertSee(route('participant.logbooks.history'))
            ->assertDontSee('/app/logbooks')
            ->assertDontSee('Buku Catatan');
    }

    public function test_logbook_and_history_pages_render(): void
    {
        $dosen = User::factory()->create(['role' => 'participant']);

        $this->actingAs($dosen)
            ->get(route('participant.logbooks'))
            ->assertOk()
            ->assertSee('Logbook terbuka setelah program aktif');

        $this->actingAs($dosen)
            ->get(route('participant.logbooks.history'))
            ->assertOk()
            ->assertSee('Riwayat Logbook');
    }
}
