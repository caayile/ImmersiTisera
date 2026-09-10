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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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
            ->assertSee('Form pendaftaran program')
            ->assertSee('surat persetujuan')
            ->assertSee('TSPM')
            ->assertSee($unit->name)
            ->assertSee($dosen->name)
            ->assertSee('Tanggal mulai')
            ->assertSee('Tanggal selesai')
            ->assertSee('Durasi otomatis 2 bulan')
            ->assertSee('Pertanyaan pendaftaran')
            ->assertSee('Mengapa Anda tertarik mengikuti program imersi')
            ->assertSee('Bagaimana hasil program akan bermanfaat')
            ->assertSee('Profil dosen')
            ->assertSee('Unggah CV')
            ->assertSee('Analytics')
            ->assertSee('AI')
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
                'motivation',
                'learning_objectives',
                'planned_activities',
                'expected_output',
                'campus_benefit',
                'cv',
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
        $this->assertSame('Ingin membawa praktik industri ke kelas dan menyusun teaching case yang relevan.', $application->motivation);
        $this->assertSame('Ingin memperdalam analitik produk digital dan proses pengambilan keputusan industri.', $application->learning_objectives);
        $this->assertSame('Observasi workflow digital, diskusi mentoring, dan penyusunan insight kurikulum.', $application->planned_activities);
        $this->assertSame('Teaching case dan modul kuliah berbasis proses industri yang diamati.', $application->expected_output);
        $this->assertSame('Mahasiswa mendapat contoh nyata industri untuk tugas dan perancangan kurikulum prodi.', $application->campus_benefit);
        $this->assertNotNull($application->cv_path);
        Storage::disk('public')->assertExists($application->cv_path);
        $this->assertNull($application->program);

        Notification::assertSentTo($admin, ImersiAlert::class);

        $this->actingAs($dosen)
            ->get(route('participant.applications.show', $application))
            ->assertOk()
            ->assertSee($application->letter_number)
            ->assertSee('Menunggu tinjauan admin')
            ->assertSee('Observasi workflow digital')
            ->assertSee('Mengapa Anda tertarik mengikuti program imersi')
            ->assertSee('Mahasiswa mendapat contoh nyata industri')
            ->assertSee('Unduh CV')
            ->assertSee('10 Sep 2026 – 10 Nov 2026')
            ->assertDontSee('Linimasa')
            ->assertDontSee('Laporan Akhir');
    }

    public function test_registration_requires_a_curriculum_vitae(): void
    {
        $this->seed();
        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();
        $payload = $this->validPayload($unit);
        unset($payload['cv']);

        $this->actingAs($dosen)
            ->from(route('participant.applications.create', ['unit' => $unit->id]))
            ->post(route('participant.applications.store'), $payload)
            ->assertRedirect(route('participant.applications.create', ['unit' => $unit->id]))
            ->assertSessionHasErrors('cv');
    }

    public function test_registration_rejects_non_document_curriculum_vitae(): void
    {
        $this->seed();
        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($dosen)
            ->from(route('participant.applications.create', ['unit' => $unit->id]))
            ->post(route('participant.applications.store'), [
                ...$this->validPayload($unit),
                'cv' => UploadedFile::fake()->image('foto.jpg', 200, 200),
            ])
            ->assertRedirect(route('participant.applications.create', ['unit' => $unit->id]))
            ->assertSessionHasErrors('cv');
    }

    public function test_registration_rejects_short_questionnaire_answers(): void
    {
        $this->seed();
        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($dosen)
            ->from(route('participant.applications.create', ['unit' => $unit->id]))
            ->post(route('participant.applications.store'), [
                ...$this->validPayload($unit),
                'campus_benefit' => 'Terlalu singkat',
            ])
            ->assertRedirect(route('participant.applications.create', ['unit' => $unit->id]))
            ->assertSessionHasErrors('campus_benefit');
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
                'motivation' => 'Motivasi <script>alert("xss")</script> untuk mengajar.',
                'planned_activities' => 'Kegiatan <img src=x onerror=alert(1)> observasi lapangan.',
                'expected_output' => 'Luaran <svg onload=alert(1)> teaching case industri.',
            ])
            ->assertRedirect();

        $application = Application::where('participant_id', $dosen->participant->id)->firstOrFail();

        $this->actingAs($dosen)
            ->get(route('participant.applications.show', $application))
            ->assertOk()
            ->assertSee('Pertanyaan 1', false)
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
        Notification::assertSentTo($dosen, ImersiAlert::class);
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
            ->put(route('participant.applications.update', $application), $this->validPayload($unit, 'Rencana kegiatan diperjelas untuk observasi dan riset terapan.'))
            ->assertRedirect(route('participant.applications.show', $application));

        $this->assertSame('submitted', $application->fresh()->status);
    }

    /**
     * @return array{business_unit_id: int, motivation: string, learning_objectives: string, planned_activities: string, expected_output: string, campus_benefit: string, period_start: string, period_end: string, declaration: string, cv: UploadedFile}
     */
    private function validPayload(BusinessUnit $unit, ?string $activities = null): array
    {
        Storage::fake('public');

        return [
            'business_unit_id' => $unit->id,
            'motivation' => 'Ingin membawa praktik industri ke kelas dan menyusun teaching case yang relevan.',
            'learning_objectives' => 'Ingin memperdalam analitik produk digital dan proses pengambilan keputusan industri.',
            'planned_activities' => $activities ?? 'Observasi workflow digital, diskusi mentoring, dan penyusunan insight kurikulum.',
            'expected_output' => 'Teaching case dan modul kuliah berbasis proses industri yang diamati.',
            'campus_benefit' => 'Mahasiswa mendapat contoh nyata industri untuk tugas dan perancangan kurikulum prodi.',
            'period_start' => '2026-09-10',
            'period_end' => '2026-11-10',
            'declaration' => '1',
            'cv' => UploadedFile::fake()->create('cv-dosen.pdf', 120, 'application/pdf'),
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
