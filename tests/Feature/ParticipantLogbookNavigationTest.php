<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantLogbookNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_applications_page_has_no_logbook_cards(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->actingAs($dosen)
            ->get(route('participant.applications'))
            ->assertOk()
            ->assertSee('Program / Pendaftaran')
            ->assertDontSee('Riwayat Logbook')
            ->assertDontSee(route('participant.logbooks.history'))
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

    public function test_logbook_pages_show_logbook_and_history_cards(): void
    {
        $dosen = User::factory()->create(['role' => 'participant']);

        foreach ([route('participant.logbooks'), route('participant.logbooks.history')] as $url) {
            $this->actingAs($dosen)
                ->get($url)
                ->assertOk()
                ->assertSee('Logbook')
                ->assertSee('Riwayat Logbook')
                ->assertSee(route('participant.logbooks'))
                ->assertSee(route('participant.logbooks.history'));
        }
    }

    public function test_participant_sidebar_has_logbook_and_history_links(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->actingAs($dosen)
            ->get(route('participant.dashboard'))
            ->assertOk()
            ->assertSee('Logbook')
            ->assertSee('Riwayat Pendaftaran')
            ->assertSee(route('participant.logbooks'));
    }

    public function test_participant_dropdown_links_dashboard_to_applications(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->actingAs($dosen)
            ->get(route('participant.dashboard'))
            ->assertOk()
            ->assertSee('Dasbor program')
            ->assertSee(route('participant.applications'));
    }
}
