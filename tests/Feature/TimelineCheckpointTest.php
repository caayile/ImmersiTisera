<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Program;
use App\Models\Timeline;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TimelineCheckpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_sees_checkpoint_weeks_and_uploads_a_report(): void
    {
        Storage::fake('public');
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $item = $program->timelines()->where('week', 2)->firstOrFail();

        $this->actingAs($dosen)
            ->get(route('participant.timeline'))
            ->assertOk()
            ->assertSee('Checkpoint Saya')
            ->assertSee('Minggu 2–4')
            ->assertSee('Isi atau perbarui checkpoint')
            ->assertSee('Dapat diisi')
            ->assertSee('Minggu 1')
            ->assertSee('Minggu 5–7')
            ->assertSee('Minggu 8')
            ->assertSee('ORIENTASI')
            ->assertSee('OBSERVASI')
            ->assertSee('KOLABORASI')
            ->assertSee('LAPORAN / HASIL')
            ->assertSee('Terkunci')
            ->assertSee('Pilih File')
            ->assertSee('Keterangan laporan (opsional)');

        $this->assertSame([1, 2, 5, 8], $program->timelines()->orderBy('week')->pluck('week')->all());
        $this->assertDatabaseHas('timelines', [
            'program_id' => $program->id,
            'week' => 1,
            'title' => 'ORIENTASI',
        ]);
        $this->assertDatabaseHas('timelines', [
            'program_id' => $program->id,
            'week' => 2,
            'title' => 'OBSERVASI',
        ]);

        $this->actingAs($dosen)
            ->post(route('participant.timeline.update', $item), [
                'title' => 'Rencana revisi minggu '.$item->week,
                'description' => 'Fokus observasi lapangan.',
                'expected_output' => 'Catatan observasi.',
                'attachment' => UploadedFile::fake()->create('laporan.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $item->refresh();
        $this->assertNotNull($item->attachment_path);
        Storage::disk('public')->assertExists($item->attachment_path);
        $this->assertDatabaseHas('timelines', [
            'id' => $item->id,
            'title' => 'Rencana revisi minggu '.$item->week,
            'status' => 'submitted',
        ]);

        $this->actingAs(User::where('email', 'mentor@imersi.id')->firstOrFail())
            ->get(route('mentor.timeline.show', $program))
            ->assertOk()
            ->assertSee('Unduh laporan terlampir');
    }

    public function test_future_checkpoint_stays_locked_and_past_checkpoint_cannot_be_edited(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $week2 = $program->timelines()->where('week', 2)->firstOrFail();
        $week5 = $program->timelines()->where('week', 5)->firstOrFail();

        // Minggu 2 berjalan: checkpoint kolaborasi (minggu 5–7) masih terkunci.
        $this->actingAs($dosen)
            ->post(route('participant.timeline.update', $week5), [
                'title' => 'Terlalu dini minggu 5',
            ])
            ->assertForbidden();

        // Masuk minggu 5: kolaborasi bisa diisi, observasi (2–4) sudah ditutup.
        $program->update([
            'start_date' => now()->subDays(28),
            'current_week' => 5,
            'status' => 'active',
        ]);

        $this->actingAs($dosen)
            ->from(route('participant.timeline'))
            ->post(route('participant.timeline.update', $week2), [
                'title' => 'Terlambat mengedit observasi',
            ])
            ->assertForbidden();

        $this->actingAs($dosen)
            ->post(route('participant.timeline.update', $week5), [
                'title' => 'Checkpoint kolaborasi aktif',
                'description' => 'Isi saat jendela minggu 5–7.',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('timelines', [
            'id' => $week5->id,
            'title' => 'Checkpoint kolaborasi aktif',
            'status' => 'submitted',
        ]);

        $this->actingAs($dosen)
            ->get(route('participant.timeline'))
            ->assertOk()
            ->assertSee('Ditutup')
            ->assertSee('Dapat diisi')
            ->assertSee('Checkpoint kolaborasi aktif');
    }

    public function test_checkpoint_accepts_word_documents_and_images(): void
    {
        Storage::fake('public');
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $uploads = [
            [2, 10, UploadedFile::fake()->create('laporan.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')],
            [5, 28, UploadedFile::fake()->image('bukti.png')],
        ];

        foreach ($uploads as [$week, $daysAgo, $upload]) {
            $program->update([
                'start_date' => now()->subDays($daysAgo),
                'current_week' => $week,
                'status' => 'active',
            ]);

            $item = $program->timelines()->where('week', $week)->firstOrFail();

            $this->actingAs($dosen)
                ->post(route('participant.timeline.update', $item), [
                    'title' => 'Checkpoint minggu '.$week,
                    'attachment' => $upload,
                ])
                ->assertRedirect()
                ->assertSessionHasNoErrors();

            $item->refresh();
            $this->assertNotNull($item->attachment_path);
            Storage::disk('public')->assertExists($item->attachment_path);
        }
    }

    public function test_checkpoint_rejects_unsupported_report_files(): void
    {
        Storage::fake('public');
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $item = $dosen->participant->programs()->latest()->firstOrFail()->timelines()->where('week', 2)->firstOrFail();

        $this->actingAs($dosen)
            ->from(route('participant.timeline'))
            ->post(route('participant.timeline.update', $item), [
                'title' => 'Checkpoint minggu 2',
                'attachment' => UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload'),
            ])
            ->assertRedirect(route('participant.timeline'))
            ->assertSessionHasErrors('attachment');
    }

    public function test_participant_cannot_submit_a_week_outside_the_checkpoint_schedule(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $legacyTimeline = $program->timelines()->create([
            'week' => 3,
            'phase' => 'observasi',
            'title' => 'Minggu 3',
            'status' => 'pending',
        ]);

        $this->actingAs($dosen)
            ->post(route('participant.timeline.update', $legacyTimeline), [
                'title' => 'Minggu 3 bukan checkpoint terpisah',
            ])
            ->assertNotFound();
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
            ->assertSee('Minggu 2–4')
            ->assertSee('ORIENTASI')
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

    public function test_observasi_window_stays_open_through_weeks_two_to_four(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $week2 = $program->timelines()->where('week', 2)->firstOrFail();

        // Minggu 3–4: checkpoint observasi (dimulai minggu 2) masih terbuka.
        $program->update([
            'start_date' => now()->subDays(21),
            'current_week' => 4,
            'status' => 'active',
        ]);

        $this->assertSame(4, $program->fresh()->computedWeek());
        $this->assertTrue($program->fresh()->isCheckpointOpen(2));
        $this->assertFalse($program->fresh()->isCheckpointOpen(1));
        $this->assertFalse($program->fresh()->isCheckpointOpen(5));

        $this->actingAs($dosen)
            ->post(route('participant.timeline.update', $week2), [
                'title' => 'Masih bisa di minggu 4',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    public function test_orientasi_only_open_in_week_one_and_hasil_only_in_week_eight(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();

        $program->update([
            'start_date' => now(),
            'current_week' => 1,
            'status' => 'active',
        ]);

        $this->assertTrue($program->fresh()->isCheckpointOpen(1));
        $this->assertFalse($program->fresh()->isCheckpointOpen(2));

        $program->update([
            'start_date' => now()->subDays(49),
            'current_week' => 8,
            'status' => 'active',
        ]);

        $this->assertFalse($program->fresh()->isCheckpointOpen(1));
        $this->assertFalse($program->fresh()->isCheckpointOpen(2));
        $this->assertFalse($program->fresh()->isCheckpointOpen(5));
        $this->assertTrue($program->fresh()->isCheckpointOpen(8));
    }

    public function test_timeline_page_repairs_legacy_checkpoint_labels_and_shows_closed_orientasi(): void
    {
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();

        $program->timelines()->where('week', 1)->delete();
        $program->timelines()->where('week', 2)->update([
            'title' => 'TEMUKAN',
            'description' => 'Lanjutkan observasi dan rangkum wawasan industri.',
            'expected_output' => 'Wawasan Industri',
            'status' => 'pending',
        ]);

        $this->actingAs($dosen)
            ->get(route('participant.timeline'))
            ->assertOk()
            ->assertSee('Minggu 1')
            ->assertSee('ORIENTASI')
            ->assertSee('Ditutup. Waktu ORIENTASI (Minggu 1) sudah lewat')
            ->assertSee('Minggu 2–4')
            ->assertSee('OBSERVASI')
            ->assertSee('Dapat diisi')
            ->assertDontSee('TEMUKAN')
            ->assertDontSee('Lanjutkan observasi dan rangkum wawasan industri.');

        $this->assertDatabaseHas('timelines', [
            'program_id' => $program->id,
            'week' => 1,
            'title' => 'ORIENTASI',
        ]);
        $this->assertDatabaseHas('timelines', [
            'program_id' => $program->id,
            'week' => 2,
            'title' => 'OBSERVASI',
        ]);
    }
}
