<?php

namespace Tests\Feature;

use App\Models\Logbook;
use App\Models\Participant;
use App\Models\Program;
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
            ->assertDontSee('/app/logbooks')
            ->assertDontSee('Buku Catatan');
    }

    public function test_logbook_page_renders(): void
    {
        $dosen = User::factory()->create(['role' => 'participant']);

        $this->actingAs($dosen)
            ->get(route('participant.logbooks'))
            ->assertOk()
            ->assertSee('Logbook terbuka setelah program aktif');
    }

    public function test_removed_history_route_returns_not_found(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();

        $this->actingAs($dosen)
            ->get('/participant/logbooks/history')
            ->assertNotFound();
    }

    public function test_logbook_input_page_renders_calendar_and_prefills_picked_date(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $picked = $program->start_date->copy()->addDays(2)->toDateString();

        $this->actingAs($dosen)
            ->get(route('participant.logbooks'))
            ->assertOk()
            ->assertSee('Klik tanggal untuk mengisi atau melihat logbook')
            ->assertSee('Apa yang dilakukan')
            ->assertSee('Apa yang dipelajari')
            ->assertSee('Apa yang ditemukan')
            ->assertSee('Kehadiran')
            ->assertSee('logbook-modal', false)
            ->assertSee('logbook-history-modal', false)
            ->assertDontSee('Selesaikan Perjanjian');

        $fresh = User::factory()->create(['role' => 'participant']);
        Participant::create(['user_id' => $fresh->id]);
        Program::create([
            'participant_id' => $fresh->participant->id,
            'mentor_id' => $program->mentor_id,
            'department_id' => $program->department_id,
            'business_unit_id' => $program->business_unit_id,
            'status' => 'submitted',
        ]);

        $this->actingAs($fresh)
            ->get(route('participant.logbooks'))
            ->assertOk()
            ->assertSee('Belum bisa diisi')
            ->assertSee('Selesaikan Perjanjian');

        $this->actingAs($dosen)
            ->get(route('participant.logbooks', ['date' => $picked]))
            ->assertOk()
            ->assertSee('value="'.$picked.'"', false);
    }

    public function test_logbook_submit_saves_attendance_and_history_shows_it(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $this->assertSame('active', $program->status);

        $date = $program->start_date->copy()->addDays(3)->toDateString();

        $this->actingAs($dosen)
            ->post(route('participant.logbooks'), [
                'entry_date' => $date,
                'attendance' => 'Izin',
                'what_did' => 'Mengikuti musyawarah jurusan.',
                'what_learned' => 'Alur birokrasi kampus.',
                'what_found' => 'Proses surat menyurat.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('logbooks', [
            'program_id' => $program->id,
            'date' => $date.' 00:00:00',
            'attendance' => 'Izin',
        ]);

        $this->actingAs($dosen)
            ->get(route('participant.logbooks', ['month' => substr($date, 0, 7)]))
            ->assertOk()
            ->assertSee('Kehadiran')
            ->assertSee('>Izin<', false)
            ->assertSee('Menunggu mentor')
            ->assertSee('loghist-'.$date, false);
    }

    public function test_input_page_groups_history_blocks_per_date(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $month = $program->start_date->copy()->startOfMonth();
        $firstDate = $month->copy()->addDays(4)->toDateString();
        $secondDate = $month->copy()->addDays(5)->toDateString();

        foreach ([$firstDate => 'Catatan khusus tanggal pertama.', $secondDate => 'Catatan khusus tanggal kedua.'] as $date => $text) {
            $program->logbooks()->create([
                'participant_id' => $program->participant_id,
                'date' => $date,
                'activity' => 'Logbook harian',
                'attendance' => 'Hadir',
                'what_i_did' => $text,
                'what_i_learned' => 'Belajar.',
                'what_i_found' => 'Temuan.',
                'status' => 'submitted',
            ]);
        }

        $this->actingAs($dosen)
            ->get(route('participant.logbooks', ['month' => $month->format('Y-m')]))
            ->assertOk()
            ->assertSee('loghist-'.$firstDate, false)
            ->assertSee('loghist-'.$secondDate, false)
            ->assertSee('Catatan khusus tanggal pertama.')
            ->assertSee('Catatan khusus tanggal kedua.');
    }

    public function test_logbook_resubmit_same_date_updates_instead_of_duplicating(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $date = $program->start_date->copy()->addDays(6)->toDateString();

        $payload = [
            'entry_date' => $date,
            'attendance' => 'Hadir',
            'what_did' => 'Isi pertama.',
            'what_learned' => 'Belajar.',
            'what_found' => 'Temuan.',
        ];

        $this->actingAs($dosen)->post(route('participant.logbooks'), $payload)->assertRedirect()->assertSessionHas('status', 'Logbook dikirim.');
        $this->actingAs($dosen)
            ->post(route('participant.logbooks'), [...$payload, 'what_did' => 'Isi revisi.'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Logbook diperbarui.');

        $this->assertSame(1, Logbook::where('program_id', $program->id)->whereDate('date', $date)->count());
        $this->assertSame('Isi revisi.', Logbook::where('program_id', $program->id)->whereDate('date', $date)->firstOrFail()->what_i_did);
    }

    public function test_input_calendar_renders_markers_and_history_blocks(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $month = $program->start_date->copy()->startOfMonth();
        $entryDate = $month->copy()->addDays(4);

        $program->logbooks()->create([
            'participant_id' => $program->participant_id,
            'date' => $entryDate->toDateString(),
            'activity' => 'Logbook harian',
            'what_i_did' => 'Observasi alur kerja unit bisnis.',
            'what_i_learned' => 'Alur sprint produk.',
            'what_i_found' => 'Data produksi.',
            'status' => 'approved',
        ]);

        $monthParam = $month->format('Y-m');
        $monthTitle = $month->copy()->locale('id')->translatedFormat('F Y');

        $this->actingAs($dosen)
            ->get(route('participant.logbooks', ['month' => $monthParam]))
            ->assertOk()
            ->assertSee($monthTitle)
            ->assertSee('Hadir disetujui')
            ->assertSee('Menunggu Tindakan Mentor')
            ->assertSee('Belum Diisi')
            ->assertSee('Bulan sebelumnya')
            ->assertSee('Observasi alur kerja unit bisnis.')
            ->assertSee('Terverifikasi')
            ->assertSee('loghist-'.$entryDate->toDateString(), false);
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
