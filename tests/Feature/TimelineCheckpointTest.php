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
            ->assertSee('Minggu '.$item->week)
            ->assertSee('Isi atau perbarui checkpoint')
            ->assertSee('Minggu 4')
            ->assertSee('Minggu 6')
            ->assertSee('Minggu 8')
            ->assertSee('Pilih File')
            ->assertSee('Keterangan laporan (opsional)');

        $this->assertSame([2, 4, 6, 8], $program->timelines()->pluck('week')->all());

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

    public function test_checkpoint_accepts_word_documents_and_images(): void
    {
        Storage::fake('public');
        $this->seed();

        $dosen = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = $dosen->participant->programs()->latest()->firstOrFail();
        $uploads = [
            [4, UploadedFile::fake()->create('laporan.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')],
            [6, UploadedFile::fake()->image('bukti.png')],
        ];

        foreach ($uploads as [$week, $upload]) {
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
            'week' => 1,
            'phase' => 'discover',
            'title' => 'Minggu 1',
            'status' => 'pending',
        ]);

        $this->actingAs($dosen)
            ->post(route('participant.timeline.update', $legacyTimeline), [
                'title' => 'Minggu 1 bukan checkpoint',
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
            ->assertSee('Minggu 2')
            ->assertDontSee('Minggu 1')
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
