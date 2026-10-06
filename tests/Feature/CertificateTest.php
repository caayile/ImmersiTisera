<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentor_can_save_signature_and_issue_certificate_for_participant(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = Program::where('participant_id', $participant->participant->id)->firstOrFail();
        $signature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $this->actingAs($mentor)
            ->get(route('mentor.certificates'))
            ->assertOk()
            ->assertSee('Tanda tangan mentor')
            ->assertSee($participant->name);

        $this->actingAs($mentor)
            ->post(route('mentor.certificates.signature'), [
                'certificate_signature' => $signature,
            ])
            ->assertRedirect();

        $this->assertSame($signature, $mentor->mentor->fresh()->certificate_signature);

        $this->actingAs($mentor)
            ->post(route('mentor.certificates.issue', $program))
            ->assertRedirect();

        $certificate = Certificate::query()->where('program_id', $program->id)->firstOrFail();
        $this->assertTrue($certificate->isIssued());
        $this->assertSame($signature, $certificate->mentor_signature);

        $this->actingAs($participant)
            ->get(route('participant.certificates'))
            ->assertOk()
            ->assertSee($certificate->number);

        $this->actingAs($participant)
            ->get(route('participant.certificates.print', $certificate))
            ->assertOk()
            ->assertSee('Sertifikat Penyelesaian')
            ->assertSee($participant->name);
    }

    public function test_mentor_cannot_issue_certificate_without_signature(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = Program::where('participant_id', $participant->participant->id)->firstOrFail();

        $mentor->mentor->update(['certificate_signature' => null]);

        $this->actingAs($mentor)
            ->from(route('mentor.certificates'))
            ->post(route('mentor.certificates.issue', $program))
            ->assertRedirect(route('mentor.certificates'))
            ->assertSessionHasErrors('certificate_signature');
    }
}
