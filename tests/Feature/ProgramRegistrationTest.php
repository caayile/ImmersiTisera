<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\Mentor;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use App\Notifications\ImersiAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProgramRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_registration_form(): void
    {
        $this->get(route('participant.applications.create'))
            ->assertRedirect(route('login'));
    }

    public function test_mentor_is_forbidden_from_participant_registration_form(): void
    {
        $this->seed();

        $this->actingAs(User::where('email', 'mentor@imersi.id')->firstOrFail())
            ->get(route('participant.applications.create'))
            ->assertForbidden();
    }

    public function test_incomplete_profile_redirects_to_profile_before_registration(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        Participant::create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('participant.applications.create'))
            ->assertRedirect(route('participant.profile'));
    }

    public function test_dosen_can_open_registration_form_with_prefilled_unit(): void
    {
        $this->seed();
        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($dosen)
            ->get(route('participant.applications.create', ['unit' => $unit->id]))
            ->assertOk()
            ->assertSee('Isi Formulir Registrasi')
            ->assertSee('Persetujuan Pemagangan')
            ->assertSee('Tahapan pendaftaran')
            ->assertSee('Selesaikan 2 langkah berikut')
            ->assertSee('Sedang mengisi')
            ->assertDontSee('Dosen mengajukan')
            ->assertDontSee('Tinjauan admin')
            ->assertDontSee('Pengesahan admin')
            ->assertDontSee('Hasil ke dosen')
            ->assertSee('TSPM')
            ->assertSee($unit->name)
            ->assertSee($dosen->name)
            ->assertSee('Tanggal mulai')
            ->assertSee('Tanggal selesai')
            ->assertSee('Durasi otomatis 2 bulan')
            ->assertSee('Informasi Program')
            ->assertSee('Detail Peserta')
            ->assertSee('Detail Lokasi')
            ->assertSee('Shared Goal')
            ->assertSee('jenis aktivitas')
            ->assertSee('Problem / Opportunity')
            ->assertSee('Main Output')
            ->assertSee('Success Indicators')
            ->assertSee('Lanjut ke persetujuan pemagangan')
            ->assertSee('Kembali ke data diri')
            ->assertSee('Kirim pendaftaran')
            ->assertSee('Profil dosen')
            ->assertDontSee('Pertanyaan pendaftaran')
            ->assertDontSee('Mengapa Anda tertarik mengikuti program imersi')
            ->assertDontSee('Kompetensi</dt>', false)
            ->assertDontSee('Keahlian</dt>', false)
            ->assertDontSee('Curriculum Vitae')
            ->assertDontSee('Unggah CV')
            ->assertDontSee('Ringkasan')
            ->assertDontSee('Linimasa')
            ->assertDontSee('<select', false);
    }

    public function test_registration_form_without_clicked_unit_redirects_to_mitra_list(): void
    {
        $this->seed();
        $dosen = $this->newDosen();

        $this->actingAs($dosen)
            ->get(route('participant.applications.create'))
            ->assertRedirect(route('departments.index'));
    }

    public function test_registration_form_requires_questionnaire_period_and_declaration(): void
    {
        $this->seed();
        $dosen = $this->newDosen();

        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($dosen)
            ->from(route('participant.applications.create', ['unit' => $unit->id]))
            ->post(route('participant.applications.store'), [])
            ->assertRedirect(route('participant.applications.create', ['unit' => $unit->id]))
            ->assertSessionHasErrors([
                'business_unit_id',
                'shared_goal',
                'activity_types',
                'problem_statement',
                'main_output',
                'participant_benefit',
                'business_benefit',
                'success_indicators',
                'period_start',
                'period_end',
                'declaration',
            ]);
    }

    public function test_dosen_can_submit_registration_and_creates_approval_letter(): void
    {
        $this->seed();
        Notification::fake();

        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();
        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();

        $this->actingAs($dosen)
            ->post(route('participant.applications.store'), $this->validPayload($unit))
            ->assertRedirect();

        $application = Application::where('participant_id', $dosen->participant->id)->firstOrFail();

        $this->assertSame('submitted', $application->status);
        $this->assertSame('IMM/'.$application->created_at->format('Y').'/'.str_pad((string) $application->id, 4, '0', STR_PAD_LEFT), $application->letter_number);
        $this->assertSame($unit->id, $application->business_unit_id);
        $this->assertSame('2026-09-10', $application->period_start->toDateString());
        $this->assertSame('2026-11-10', $application->period_end->toDateString());
        $this->assertSame('10 Sep 2026 – 10 Nov 2026', $application->preferred_period);
        $this->assertSame('Selama 2 bulan, kami akan memetakan workflow digital untuk menghasilkan teaching case yang memberikan manfaat bagi mahasiswa Informatika.', $application->shared_goal);
        $this->assertSame(['observasi', 'riset'], $application->normalizedActivityTypes());
        $this->assertSame('Observasi workflow digital, diskusi mentoring, dan penyusunan insight kurikulum untuk riset terapan.', $application->problem_statement);
        $this->assertSame('Research Report + Prototype Concept modul kuliah digital.', $application->main_output);
        $this->assertSame('Dosen mendapat studi kasus nyata untuk bahan ajar dan riset terapan.', $application->participant_benefit);
        $this->assertSame('Unit bisnis mendapat sudut pandang akademik atas proses digitalnya.', $application->business_benefit);
        $this->assertSame(['Teaching case selesai dan divalidasi mentor', 'Modul kuliah baru dipakai satu semester'], $application->normalizedSuccessIndicators());
        $this->assertNull($application->cv_path);
        $this->assertNull($application->program);

        Notification::assertSentTo($admin, ImersiAlert::class);

        $this->actingAs($dosen)
            ->get(route('participant.applications.show', $application))
            ->assertOk()
            ->assertSee($application->letter_number)
            ->assertSee('Menunggu tinjauan admin')
            ->assertSee('Persetujuan Pemagangan')
            ->assertSee('Shared Goal')
            ->assertSee('Observasi workflow digital')
            ->assertSee('Research Report + Prototype Concept')
            ->assertSee('Teaching case selesai dan divalidasi mentor')
            ->assertSee('10 Sep 2026 – 10 Nov 2026')
            ->assertDontSee('Linimasa')
            ->assertDontSee('Laporan Akhir');
    }

    public function test_dosen_can_reopen_submitted_application_to_view_data_but_cannot_resubmit(): void
    {
        $this->seed();
        $application = $this->submittedApplication();
        $dosen = $application->participant->user;

        $this->actingAs($dosen)
            ->get(route('participant.applications.show', $application))
            ->assertOk()
            ->assertSee('Lihat data pendaftaran')
            ->assertSee('Lihat data')
            ->assertSee('Sedang berjalan');

        $this->actingAs($dosen)
            ->get(route('participant.applications.edit', $application))
            ->assertOk()
            ->assertSee('Data pendaftaran (hanya lihat)')
            ->assertSee('Mode hanya baca')
            ->assertSee('Pengiriman dikunci')
            ->assertSee('Tahap sebelumnya dapat dilihat di bawah')
            ->assertSee('2026-09-10')
            ->assertSee('2026-11-10')
            ->assertSee('Persetujuan Pemagangan')
            ->assertSee('Shared Goal')
            ->assertDontSee('Kirim pendaftaran')
            ->assertDontSee('Kirim ulang ke admin');
    }

    public function test_dosen_cannot_resubmit_application_unless_admin_requests_revision(): void
    {
        $this->seed();
        $application = $this->submittedApplication();
        $dosen = $application->participant->user;
        $unit = $application->businessUnit;

        $this->actingAs($dosen)
            ->put(route('participant.applications.update', $application), $this->validPayload($unit, 'Percobaan kirim ulang tanpa revisi.'))
            ->assertForbidden();

        $this->assertSame('submitted', $application->fresh()->status);
    }

    public function test_registration_rejects_short_persetujuan_answers(): void
    {
        $this->seed();
        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($dosen)
            ->from(route('participant.applications.create', ['unit' => $unit->id]))
            ->post(route('participant.applications.store'), [
                ...$this->validPayload($unit),
                'business_benefit' => 'Terlalu singkat',
            ])
            ->assertRedirect(route('participant.applications.create', ['unit' => $unit->id]))
            ->assertSessionHasErrors('business_benefit');
    }

    public function test_registration_rejects_period_that_is_not_two_months(): void
    {
        $this->seed();
        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($dosen)
            ->from(route('participant.applications.create', ['unit' => $unit->id]))
            ->post(route('participant.applications.store'), [
                ...$this->validPayload($unit),
                'period_end' => '2026-10-10',
            ])
            ->assertRedirect(route('participant.applications.create', ['unit' => $unit->id]))
            ->assertSessionHasErrors('period_end');
    }

    public function test_dosen_cannot_submit_second_open_registration(): void
    {
        $this->seed();
        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($dosen)->post(route('participant.applications.store'), $this->validPayload($unit));

        $this->actingAs($dosen)
            ->from(route('participant.applications.create'))
            ->post(route('participant.applications.store'), $this->validPayload($unit))
            ->assertRedirect(route('participant.applications.create'))
            ->assertSessionHasErrors('business_unit_id');
    }

    public function test_approval_letter_escapes_user_supplied_text(): void
    {
        $this->seed();
        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($dosen)
            ->post(route('participant.applications.store'), [
                ...$this->validPayload($unit),
                'shared_goal' => 'Tujuan <script>alert("xss")</script> untuk memetakan workflow.',
                'problem_statement' => 'Kegiatan <img src=x onerror=alert(1)> observasi lapangan industri.',
                'main_output' => 'Luaran <svg onload=alert(1)> teaching case industri digital.',
            ])
            ->assertRedirect();

        $application = Application::where('participant_id', $dosen->participant->id)->firstOrFail();

        $this->actingAs($dosen)
            ->get(route('participant.applications.show', $application))
            ->assertOk()
            ->assertSee('Bagian 1', false)
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertDontSee('<img src=x onerror=alert(1)>', false)
            ->assertDontSee('<svg onload=alert(1)>', false);
    }

    public function test_dosen_cannot_view_another_participant_letter(): void
    {
        $this->seed();
        $maya = User::where('email', 'dosen2@imersi.id')->firstOrFail();
        $application = Application::where('participant_id', $maya->participant->id)->firstOrFail();

        $this->actingAs(User::where('email', 'dosen@imersi.id')->firstOrFail())
            ->get(route('participant.applications.show', $application))
            ->assertForbidden();
    }

    public function test_admin_first_review_forwards_to_mentor_without_creating_program(): void
    {
        $this->seed();
        Notification::fake();

        $application = $this->submittedApplication();
        $mentor = Mentor::whereHas('user', fn ($q) => $q->where('email', 'mentor@imersi.id'))->firstOrFail();

        $this->actingAs(User::where('email', 'admin@imersi.id')->firstOrFail())
            ->post(route('admin.matching.update', $application), [
                'status' => 'approved',
                'mentor_id' => $mentor->id,
                'business_unit_id' => $application->business_unit_id,
                'matching_notes' => 'Diteruskan ke mentor Digital Business.',
            ])
            ->assertRedirect();

        $application->refresh();
        $this->assertSame('waiting_mentor', $application->status);
        $this->assertNotNull($application->admin_reviewed_at);
        $this->assertNull(Program::where('application_id', $application->id)->first());
        Notification::assertSentTo($mentor->user, ImersiAlert::class);
    }

    public function test_admin_reforward_after_forward_redirects_with_info_instead_of_error(): void
    {
        $this->seed();

        $application = $this->submittedApplication();
        $mentor = Mentor::whereHas('user', fn ($q) => $q->where('email', 'mentor@imersi.id'))->firstOrFail();
        $payload = [
            'status' => 'approved',
            'mentor_id' => $mentor->id,
            'business_unit_id' => $application->business_unit_id,
        ];

        $this->actingAs(User::where('email', 'admin@imersi.id')->firstOrFail())
            ->post(route('admin.matching.update', $application), $payload)
            ->assertRedirect();

        $this->assertSame('waiting_mentor', $application->fresh()->status);

        $this->actingAs(User::where('email', 'admin@imersi.id')->firstOrFail())
            ->post(route('admin.matching.update', $application), $payload)
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame('waiting_mentor', $application->fresh()->status);
    }

    public function test_mentor_cannot_review_before_admin_forwards_application(): void
    {
        $this->seed();
        $application = $this->submittedApplication();

        $this->actingAs(User::where('email', 'mentor@imersi.id')->firstOrFail())
            ->post(route('mentor.applications.review', $application), [
                'decision' => 'approved',
            ])
            ->assertStatus(422);
    }

    public function test_other_mentor_cannot_review_assigned_application(): void
    {
        $this->seed();
        $application = $this->submittedApplication();
        $mentor = Mentor::whereHas('user', fn ($q) => $q->where('email', 'mentor@imersi.id'))->firstOrFail();

        $this->actingAs(User::where('email', 'admin@imersi.id')->firstOrFail())
            ->post(route('admin.matching.update', $application), [
                'status' => 'approved',
                'mentor_id' => $mentor->id,
                'business_unit_id' => $application->business_unit_id,
            ]);

        $this->actingAs(User::where('email', 'mentor-it@imersi.id')->firstOrFail())
            ->post(route('mentor.applications.review', $application), [
                'decision' => 'approved',
            ])
            ->assertForbidden();
    }

    public function test_full_approval_flow_creates_program_and_notifies_dosen(): void
    {
        $this->seed();
        Notification::fake();

        $application = $this->submittedApplication();
        $dosen = $application->participant->user;
        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $mentor = Mentor::whereHas('user', fn ($q) => $q->where('email', 'mentor@imersi.id'))->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.matching.update', $application), [
                'status' => 'approved',
                'mentor_id' => $mentor->id,
                'business_unit_id' => $application->business_unit_id,
            ])
            ->assertRedirect();

        $this->actingAs($mentor->user)
            ->post(route('mentor.applications.review', $application), [
                'decision' => 'approved',
                'mentor_note' => 'Siap menerima dosen.',
            ])
            ->assertRedirect();

        $this->assertSame('waiting_admin', $application->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.matching.update', $application), [
                'status' => 'approved',
                'mentor_id' => $mentor->id,
                'business_unit_id' => $application->business_unit_id,
                'matching_notes' => 'Disahkan.',
            ])
            ->assertRedirect();

        $application->refresh();
        $this->assertSame('approved', $application->status);
        $this->assertNotNull($application->admin_finalized_at);
        $this->assertNotNull($application->program);
        $this->assertSame('draft', $application->program->agreement->status);
        $this->assertSame($application->shared_goal, $application->program->agreement->objective);
        $this->assertSame($application->problem_statement, $application->program->agreement->problem_statement);
        $this->assertSame($application->normalizedSuccessIndicators(), $application->program->agreement->success_indicators);
        Notification::assertSentTo($dosen, ImersiAlert::class);
    }

    public function test_mentor_reapprove_after_approve_redirects_with_info_instead_of_error(): void
    {
        $this->seed();

        $application = $this->submittedApplication();
        $mentor = Mentor::whereHas('user', fn ($q) => $q->where('email', 'mentor@imersi.id'))->firstOrFail();

        $this->actingAs(User::where('email', 'admin@imersi.id')->firstOrFail())
            ->post(route('admin.matching.update', $application), [
                'status' => 'approved',
                'mentor_id' => $mentor->id,
                'business_unit_id' => $application->business_unit_id,
            ])
            ->assertRedirect();

        $this->actingAs($mentor->user)
            ->post(route('mentor.applications.review', $application), ['decision' => 'approved'])
            ->assertRedirect();

        $this->assertSame('waiting_admin', $application->fresh()->status);

        $this->actingAs($mentor->user)
            ->post(route('mentor.applications.review', $application), ['decision' => 'approved'])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame('waiting_admin', $application->fresh()->status);
    }

    public function test_admin_revision_lets_dosen_resubmit_to_admin(): void
    {
        $this->seed();
        $application = $this->submittedApplication();
        $dosen = $application->participant->user;
        $unit = $application->businessUnit;

        $this->actingAs(User::where('email', 'admin@imersi.id')->firstOrFail())
            ->post(route('admin.matching.update', $application), [
                'status' => 'revision',
                'mentor_id' => $application->mentor_id,
                'business_unit_id' => $unit->id,
                'matching_notes' => 'Perjelas rencana kegiatan.',
            ])
            ->assertRedirect();

        $this->assertSame('revision', $application->fresh()->status);

        $this->actingAs($dosen)
            ->put(route('participant.applications.update', $application), $this->validPayload($unit, 'Rencana kegiatan diperjelas untuk observasi dan riset terapan di lapangan.'))
            ->assertRedirect(route('participant.applications.show', $application));

        $this->assertSame('submitted', $application->fresh()->status);
    }

    public function test_mentor_can_edit_indicators_and_send_back_to_participant(): void
    {
        $this->seed();
        Notification::fake();

        $application = $this->submittedApplication();
        $dosen = $application->participant->user;
        $unit = $application->businessUnit;
        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();
        $mentor = Mentor::whereHas('user', fn ($q) => $q->where('email', 'mentor@imersi.id'))->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.matching.update', $application), [
                'status' => 'approved',
                'mentor_id' => $mentor->id,
                'business_unit_id' => $application->business_unit_id,
            ])
            ->assertRedirect();

        $this->assertSame('waiting_mentor', $application->fresh()->status);

        $proposed = ['Prototype tervalidasi bersama tim industri', 'Laporan riset selesai dan dipresentasikan'];

        $this->actingAs($mentor->user)
            ->post(route('mentor.applications.review', $application), [
                'decision' => 'revision',
                'mentor_note' => 'Indikator kurang terukur, saya usulkan versi baru.',
                'success_indicators' => $proposed,
            ])
            ->assertRedirect();

        $application->refresh();
        $this->assertSame('revision', $application->status);
        $this->assertSame($proposed, $application->normalizedSuccessIndicators());
        $this->assertSame('Indikator kurang terukur, saya usulkan versi baru.', $application->revision_note);

        Notification::assertSentTo($dosen, ImersiAlert::class);

        $this->actingAs($dosen)
            ->get(route('participant.applications.edit', $application))
            ->assertOk()
            ->assertSee('Prototype tervalidasi bersama tim industri')
            ->assertSee('Feedback untuk usulan mentor');

        $this->actingAs($dosen)
            ->put(route('participant.applications.update', $application), [
                ...$this->validPayload($unit),
                'success_indicators' => [...$proposed, 'Modul kuliah baru dipakai satu semester penuh'],
                'indicator_feedback' => 'Setuju dengan usulan mentor, saya tambah satu indikator adopsi modul.',
            ])
            ->assertRedirect(route('participant.applications.show', $application));

        $application->refresh();
        $this->assertSame('submitted', $application->status);
        $this->assertCount(3, $application->normalizedSuccessIndicators());
        $this->assertSame('Setuju dengan usulan mentor, saya tambah satu indikator adopsi modul.', $application->indicator_feedback);
    }

    /**
     * @return array{business_unit_id: int, shared_goal: string, activity_types: list<string>, problem_statement: string, main_output: string, participant_benefit: string, business_benefit: string, success_indicators: list<string>, period_start: string, period_end: string, declaration: string}
     */
    private function validPayload(BusinessUnit $unit, ?string $problem = null): array
    {
        return [
            'business_unit_id' => $unit->id,
            'shared_goal' => 'Selama 2 bulan, kami akan memetakan workflow digital untuk menghasilkan teaching case yang memberikan manfaat bagi mahasiswa Informatika.',
            'activity_types' => ['observasi', 'riset'],
            'problem_statement' => $problem ?? 'Observasi workflow digital, diskusi mentoring, dan penyusunan insight kurikulum untuk riset terapan.',
            'main_output' => 'Research Report + Prototype Concept modul kuliah digital.',
            'participant_benefit' => 'Dosen mendapat studi kasus nyata untuk bahan ajar dan riset terapan.',
            'business_benefit' => 'Unit bisnis mendapat sudut pandang akademik atas proses digitalnya.',
            'success_indicators' => ['Teaching case selesai dan divalidasi mentor', 'Modul kuliah baru dipakai satu semester'],
            'period_start' => '2026-09-10',
            'period_end' => '2026-11-10',
            'declaration' => '1',
        ];
    }

    private function newDosen(): User
    {
        $user = User::factory()->create([
            'role' => 'participant',
            'status' => 'active',
            'verification_status' => 'verified',
        ]);

        Participant::create([
            'user_id' => $user->id,
            'nidn' => '0011223344',
            'faculty' => 'Fakultas Teknik',
            'study_program' => 'Informatika',
            'expertise' => ['AI'],
            'competency' => ['Analytics'],
            'motivation' => 'Memahami praktik industri secara langsung.',
        ]);

        return $user->fresh('participant');
    }

    private function submittedApplication(): Application
    {
        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($dosen)->post(route('participant.applications.store'), $this->validPayload($unit));

        return Application::where('participant_id', $dosen->participant->id)->firstOrFail();
    }
}
