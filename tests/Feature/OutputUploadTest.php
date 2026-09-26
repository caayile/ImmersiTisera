<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OutputUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_upload_documentation_and_mentor_can_access_it(): void
    {
        Storage::fake('public');
        $this->seed();

        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $program = Program::where('participant_id', $participant->participant->id)->firstOrFail();

        $this->actingAs($participant)
            ->get(route('participant.outputs'))
            ->assertOk()
            ->assertSee('Upload hasil kerja atau bukti dokumentasi')
            ->assertSee('File hasil / bukti');

        $this->actingAs($participant)
            ->post('/participant/outputs', [
                'title' => 'Bukti Dokumentasi Observasi',
                'type' => 'Bukti Dokumentasi',
                'description' => 'Dokumentasi kegiatan observasi lapangan.',
                'file' => UploadedFile::fake()->create('observasi.pdf', 200, 'application/pdf'),
            ])
            ->assertRedirect();

        $output = $program->outputs()->latest()->firstOrFail();
        Storage::disk('public')->assertExists($output->file_path);

        $this->actingAs($mentor)
            ->get(route('mentor.outputs'))
            ->assertOk()
            ->assertSee('Bukti Dokumentasi Observasi')
            ->assertSee('Lihat / unduh bukti');
    }
}
