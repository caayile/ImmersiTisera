<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\CertificateSignatory;
use App\Models\EvaluationQuestion;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use App\Services\CertificateIssuer;
use App\Services\CertificatePdfRenderer;
use App\Services\CertificateSignatureStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentor_saves_signature_grades_and_publishes_certificate_in_one_flow(): void
    {
        $this->seed();
        Storage::fake('local');

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
            ->assertSee('Ulangi')
            ->assertSee($participant->name);

        $this->actingAs($mentor)
            ->post(route('mentor.certificates.signature'), [
                'certificate_signature' => $signature,
            ])
            ->assertRedirect();

        $signaturePath = $mentor->mentor->fresh()->certificate_signature;
        $this->assertStringStartsWith('certificates/signatures/', $signaturePath);
        Storage::disk('local')->assertExists($signaturePath);

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
        $this->assertNotSame($signaturePath, $certificate->mentor_signature_path);
        Storage::disk('local')->assertExists($certificate->mentor_signature_path);
        $this->assertSame('Dr. Andi Pratama', $certificate->snapshot['participant_name']);
        $this->assertSame($program->department->name, $certificate->snapshot['department_name']);
        $this->assertSame($program->businessUnit->name, $certificate->snapshot['business_unit_name']);
        $this->assertSame('Budi Santoso', $certificate->snapshot['mentor_name']);
        $this->assertSame('Erfan Zaenuddin', $certificate->signatory_snapshot['name']);
        app(CertificateSignatureStorage::class)->store(null, $signature, $signaturePath);
        Storage::disk('local')->assertMissing($signaturePath);
        Storage::disk('local')->assertExists($certificate->mentor_signature_path);

        $this->actingAs($participant)
            ->get(route('participant.certificates'))
            ->assertRedirect(route('participant.evaluation').'#sertifikat');

        // Nilai + sertifikat terkunci sampai peserta mengisi kuesioner balik.
        $this->actingAs($participant)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee('Nilai raport dan sertifikat terkunci sampai kuesioner diisi')
            ->assertDontSee($certificate->number)
            ->assertDontSee('Lihat / Cetak');

        $this->actingAs($participant)
            ->post(route('participant.evaluation'), [
                'criteria' => collect(EvaluationQuestion::defaultBank())
                    ->flatMap(fn ($section) => $section['questions'])
                    ->map(fn ($question) => ['label' => $question, 'score' => 5])
                    ->all(),
            ])
            ->assertRedirect(route('participant.evaluation').'#hasil');

        $this->actingAs($participant)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee($certificate->number)
            ->assertSee('Sertifikat penyelesaian')
            ->assertSee('Lihat / Cetak');

        $this->actingAs($participant)
            ->get(route('participant.certificates.print', $certificate))
            ->assertOk()
            ->assertSee('SERTIFIKAT')
            ->assertSee($participant->name);

        $chromePath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';

        if (is_file($chromePath)) {
            config(['services.browsershot.chrome_path' => null]);
        } else {
            $this->mock(CertificatePdfRenderer::class)
                ->shouldReceive('render')
                ->once()
                ->withArgs(fn (string $html): bool => str_contains($html, $certificate->number))
                ->andReturn('%PDF-1.7 certificate');
        }

        $pdfResponse = $this->actingAs($participant)
            ->get(route('participant.certificates.download', $certificate))
            ->assertDownload('sertifikat-'.$certificate->global_sequence.'.pdf')
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        $otherUser = User::factory()->create(['role' => 'participant']);
        Participant::create(['user_id' => $otherUser->id]);

        $this->actingAs($otherUser)
            ->get(route('participant.certificates.print', $certificate))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->get(route('participant.certificates.download', $certificate))
            ->assertForbidden();
    }

    public function test_certificate_numbers_are_global_sequential_and_repeat_issuance_is_idempotent(): void
    {
        $this->seed();
        $this->travelTo('2026-10-16 09:00:00');

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail()->mentor;
        $program = Program::whereHas('mentor.user', fn ($query) => $query->where('email', 'mentor@imersi.id'))->firstOrFail();
        $secondProgram = Program::whereHas('mentor.user', fn ($query) => $query->where('email', 'mentor-coe@imersi.id'))->firstOrFail();
        $mentor->update(['certificate_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==']);
        $secondMentor = $secondProgram->mentor;
        $secondMentor->update(['certificate_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==']);
        $issuer = app(CertificateIssuer::class);

        $firstCertificate = $issuer->issue($program, $mentor);
        $repeatCertificate = $issuer->issue($program, $mentor);
        $secondCertificate = $issuer->issue($secondProgram, $secondMentor);

        $this->assertSame('NO. 001/MD.TSU/B1/X/2026', $firstCertificate->number);
        $this->assertSame($firstCertificate->number, $repeatCertificate->number);
        $this->assertSame('NO. 002/MD.TSU/B1/X/2026', $secondCertificate->number);
        $this->assertSame($secondProgram->mentor->user->name, $secondCertificate->snapshot['mentor_name']);
        $this->assertSame($secondProgram->department->name, $secondCertificate->snapshot['department_name']);
        $this->assertSame($secondProgram->businessUnit->name, $secondCertificate->snapshot['business_unit_name']);
        $this->assertSame(2, Certificate::query()->count());
        $this->assertSame([1, 2], Certificate::query()->orderBy('global_sequence')->pluck('global_sequence')->all());
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
            ->assertRedirect()
            ->assertSessionHasNoErrors();

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

    public function test_signature_upload_validates_format_and_stores_file_privately(): void
    {
        $this->seed();
        Storage::fake('local');

        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $validSignature = UploadedFile::fake()->image('signature.png');

        $this->actingAs($mentor)
            ->post(route('mentor.certificates.signature'), ['certificate_signature_file' => $validSignature])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $signaturePath = $mentor->mentor->fresh()->certificate_signature;
        $this->assertStringStartsWith('certificates/signatures/', $signaturePath);
        Storage::disk('local')->assertExists($signaturePath);

        $this->actingAs($mentor)
            ->post(route('mentor.certificates.signature'), [
                'certificate_signature_file' => UploadedFile::fake()->createWithContent('signature.txt', 'not an image'),
            ])
            ->assertSessionHasErrors('certificate_signature_file');

        $this->actingAs($mentor)
            ->post(route('mentor.certificates.signature'), [
                'certificate_signature_file' => UploadedFile::fake()->image('large-signature.png')->size(1025),
            ])
            ->assertSessionHasErrors('certificate_signature_file');
    }

    public function test_admin_can_update_a_mentor_signature_and_signatory_details(): void
    {
        $this->seed();
        Storage::fake('local');

        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail()->mentor;

        $this->actingAs($admin)
            ->post(route('admin.mentors.certificate-signature', $mentor), [
                'certificate_signature_file' => UploadedFile::fake()->image('mentor-signature.webp'),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $mentorSignature = $mentor->fresh()->certificate_signature;
        Storage::disk('local')->assertExists($mentorSignature);

        $this->actingAs($admin)
            ->put(route('admin.settings.certificate-signatory'), [
                'name' => 'Nama Pejabat Baru',
                'title' => 'Direktur',
                'organization' => 'PT Tiga Serangkai',
                'signature_file' => UploadedFile::fake()->image('signatory.png'),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('certificate_signatories', [
            'name' => 'Nama Pejabat Baru',
            'title' => 'Direktur',
            'organization' => 'PT Tiga Serangkai',
        ]);

        $signatory = CertificateSignatory::query()->where('is_active', true)->firstOrFail();
        $sourceSignaturePath = $signatory->signature_path;
        Storage::disk('local')->assertExists($sourceSignaturePath);
        $program = Program::whereHas('mentor.user', fn ($query) => $query->where('email', 'mentor@imersi.id'))->firstOrFail();
        $certificate = app(CertificateIssuer::class)->issue($program, $program->mentor);
        $snapshotSignaturePath = $certificate->signatory_snapshot['signature_path'];

        $this->assertNotSame($sourceSignaturePath, $snapshotSignaturePath);
        Storage::disk('local')->assertExists($snapshotSignaturePath);

        $this->actingAs($admin)
            ->put(route('admin.settings.certificate-signatory'), [
                'name' => 'Pejabat Pengganti',
                'title' => 'Direktur Utama',
                'organization' => 'PT TSU',
                'signature_file' => UploadedFile::fake()->image('signatory-new.png'),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        Storage::disk('local')->assertMissing($sourceSignaturePath);
        Storage::disk('local')->assertExists($snapshotSignaturePath);
        $this->assertSame('Nama Pejabat Baru', $certificate->fresh()->signatory_snapshot['name']);

        $this->actingAs(User::where('email', 'mentor@imersi.id')->firstOrFail())
            ->post(route('admin.mentors.certificate-signature', $mentor))
            ->assertForbidden();
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
