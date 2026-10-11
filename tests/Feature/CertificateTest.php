<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\EvaluationQuestion;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentor_saves_signature_grades_and_publishes_certificate_in_one_flow(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = Program::where('participant_id', $participant->participant->id)->firstOrFail();
        $signature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $this->actingAs($mentor)
            ->get(route('mentor.penilaian'))
            ->assertOk()
            ->assertSee('Penilaian')
            ->assertSee($participant->name);

        $this->actingAs($mentor)
            ->get(route('mentor.penilaian.show', $program))
            ->assertOk()
            ->assertSee('Tanda tangan sertifikat')
            ->assertSee($participant->name);

        $this->actingAs($mentor)
            ->post(route('mentor.certificates.signature'), [
                'certificate_signature' => $signature,
            ])
            ->assertRedirect();

        $this->assertSame($signature, $mentor->mentor->fresh()->certificate_signature);

        $this->actingAs($mentor)
            ->post(route('mentor.evaluations.store', $program), [
                'groups' => [
                    [
                        'name' => 'Project',
                        'weight' => 40,
                        'aspects' => [['label' => 'Kualitas Hasil Kerja', 'score' => 90]],
                    ],
                    [
                        'name' => 'Sikap',
                        'weight' => 60,
                        'aspects' => [['label' => 'Kehadiran', 'score' => 95]],
                    ],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($mentor)
            ->post(route('mentor.evaluations.approve', $program))
            ->assertRedirect();

        $certificate = Certificate::query()->where('program_id', $program->id)->firstOrFail();
        $this->assertTrue($certificate->isIssued());
        $this->assertSame($signature, $certificate->mentor_signature);

        $this->actingAs($participant)
            ->get(route('participant.certificates'))
            ->assertRedirect(route('participant.evaluation'));

        // Nilai + sertifikat terkunci sampai peserta mengisi kuesioner balik.
        $this->actingAs($participant)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee('Nilai raport terkunci sampai kuesioner diisi')
            ->assertDontSee($certificate->number);

        $this->actingAs($participant)
            ->post(route('participant.evaluation'), [
                'criteria' => collect(EvaluationQuestion::defaultBank())
                    ->flatMap(fn ($section) => $section['questions'])
                    ->map(fn ($question) => ['label' => $question, 'score' => 5])
                    ->all(),
            ])
            ->assertRedirect();

        $this->actingAs($participant)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee($certificate->number);

        $this->actingAs($participant)
            ->get(route('participant.certificates.print', $certificate))
            ->assertOk()
            ->assertSee('Sertifikat Penyelesaian')
            ->assertSee($participant->name);
    }

    public function test_mentor_can_approve_report_and_issue_certificate_together(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = Program::where('participant_id', $participant->participant->id)->firstOrFail();
        $mentor->mentor->update(['certificate_signature' => null]);

        // Belum ada nilai → 404.
        $this->actingAs($mentor)
            ->post(route('mentor.evaluations.approve', $program))
            ->assertNotFound();

        // Isi nilai raport dulu.
        $this->actingAs($mentor)
            ->post(route('mentor.evaluations.store', $program), [
                'groups' => [
                    [
                        'name' => 'Project',
                        'weight' => 40,
                        'aspects' => [['label' => 'Kualitas Hasil Kerja', 'score' => 90]],
                    ],
                    [
                        'name' => 'Sikap',
                        'weight' => 60,
                        'aspects' => [['label' => 'Kehadiran', 'score' => 95]],
                    ],
                ],
            ])
            ->assertRedirect();

        // Tanpa tanda tangan → ditolak.
        $this->actingAs($mentor)
            ->post(route('mentor.evaluations.approve', $program))
            ->assertSessionHasErrors('certificate_signature');

        $this->actingAs($mentor)
            ->post(route('mentor.certificates.signature'), [
                'certificate_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
            ])
            ->assertRedirect();

        // Pengesahan bersama menerbitkan sertifikat.
        $this->actingAs($mentor)
            ->post(route('mentor.evaluations.approve', $program))
            ->assertRedirect();

        $certificate = Certificate::query()->where('program_id', $program->id)->firstOrFail();
        $this->assertTrue($certificate->isIssued());

        $this->actingAs($mentor)
            ->get(route('mentor.evaluations'))
            ->assertOk()
            ->assertSee('Raport disahkan')
            ->assertSee($certificate->number);
    }

    public function test_mentor_cannot_open_other_mentors_penilaian_detail(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $other = User::where('email', 'mentor-it@imersi.id')->firstOrFail();
        $program = Program::whereHas('mentor.user', fn ($query) => $query->whereKey($mentor->id))->firstOrFail();

        $this->actingAs($other)
            ->get(route('mentor.penilaian.show', $program))
            ->assertForbidden();

        $this->actingAs($other)
            ->post(route('mentor.evaluations.store', $program), ['groups' => []])
            ->assertForbidden();
    }

    public function test_standalone_certificate_issue_without_grades_is_removed(): void
    {
        $this->seed();

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $program = Program::where('participant_id', $participant->participant->id)->firstOrFail();

        $mentor->mentor->update([
            'certificate_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
        ]);

        // Jalur terbit terpisah sudah dihapus: sertifikat hanya terbit
        // lewat pengesahan raport. URL lama harus 404 dan tidak
        // menerbitkan apa pun.
        $this->actingAs($mentor)
            ->post('/mentor/certificates/'.$program->id.'/issue')
            ->assertNotFound();

        $this->assertDatabaseMissing('certificates', ['program_id' => $program->id]);

        // Halaman sertifikat lama mengarah ke Penilaian.
        $this->actingAs($mentor)
            ->get(route('mentor.certificates'))
            ->assertRedirect(route('mentor.penilaian'));
    }
}
