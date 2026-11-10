<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OutputUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_upload_documentation_and_mentor_can_access_it(): void
    {
        Storage::fake('local');
        $this->seed();

        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $program = Program::where('participant_id', $participant->participant->id)->firstOrFail();

        $this->actingAs($participant)
            ->get(route('participant.outputs'))
            ->assertRedirect(route('participant.final-report'));

        $this->actingAs($participant)
            ->post('/participant/outputs', [
                'title' => 'Bukti Dokumentasi Observasi',
                'type' => 'Bukti Dokumentasi',
                'description' => 'Dokumentasi kegiatan observasi lapangan.',
                'file' => UploadedFile::fake()->create('observasi.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect();

        $output = $program->outputs()->latest()->firstOrFail();
        Storage::disk('local')->assertExists($output->file_path);

        $this->actingAs($mentor)
            ->get(route('mentor.participants.show', $program))
            ->assertOk()
            ->assertSee('Laporan Akhir')
            ->assertSee($participant->name)
            ->assertSee('Bukti Dokumentasi Observasi');

        $output = $program->outputs()->latest()->firstOrFail();

        $this->actingAs($mentor)
            ->post(route('mentor.outputs.review', $output), [
                'status' => 'approved',
                'mentor_feedback' => 'Baik.',
            ])
            ->assertRedirect();
        $this->assertSame('approved', $output->fresh()->status);
    }

    public function test_private_output_file_download_is_limited_to_program_members(): void
    {
        Storage::fake('local');
        $this->seed();

        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $otherParticipant = User::where('email', 'dosen2@imersi.id')->firstOrFail();
        $program = Program::where('participant_id', $participant->participant->id)->firstOrFail();
        $path = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf')->store('outputs', 'local');
        $output = $program->outputs()->create([
            'participant_id' => $participant->participant->id,
            'title' => 'Laporan privat',
            'type' => 'Research Report',
            'file_path' => $path,
            'status' => 'submitted',
        ]);
        $downloadUrl = '/private-files/outputs/'.$output->id.'/report';

        $this->actingAs($otherParticipant)->get($downloadUrl)->assertForbidden();
        $this->actingAs($participant)->get($downloadUrl)->assertOk();
    }

    public function test_legacy_public_output_files_are_dry_run_then_migrated(): void
    {
        Storage::fake('public');
        Storage::fake('local');
        $this->seed();

        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = Program::where('participant_id', $participant->participant->id)->firstOrFail();
        $path = 'outputs/legacy-report.pdf';
        Storage::disk('public')->put($path, 'legacy private report');
        $program->outputs()->create([
            'participant_id' => $participant->participant->id,
            'title' => 'Legacy report',
            'type' => 'Research Report',
            'file_path' => $path,
            'status' => 'submitted',
        ]);

        Artisan::call('files:secure-uploads');
        Storage::disk('public')->assertExists($path);
        Storage::disk('local')->assertMissing($path);

        Artisan::call('files:secure-uploads', ['--apply' => true]);
        Storage::disk('public')->assertMissing($path);
        Storage::disk('local')->assertExists($path);
        $this->assertSame('legacy private report', Storage::disk('local')->get($path));
    }
}
