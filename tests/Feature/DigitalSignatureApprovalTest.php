<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\Mentor;
use App\Models\Participant;
use App\Models\User;
use App\Notifications\ImersiAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DigitalSignatureApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_at_least_two_success_indicators(): void
    {
        $this->seed();
        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $this->actingAs($dosen)
            ->from(route('participant.applications.create', ['unit' => $unit->id]))
            ->post(route('participant.applications.store'), [
                ...$this->validPayload($unit),
                'success_indicators' => ['Hanya satu indikator yang cukup panjang'],
            ])
            ->assertRedirect(route('participant.applications.create', ['unit' => $unit->id]))
            ->assertSessionHasErrors('success_indicators');
    }

    public function test_dosen_can_add_more_than_three_success_indicators(): void
    {
        $this->seed();
        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();

        $indicators = [
            'Teaching case selesai dan divalidasi mentor',
            'Modul kuliah baru dipakai satu semester',
            'Prototype tervalidasi bersama tim industri',
            'Laporan riset dipresentasikan ke unit bisnis',
        ];

        $this->actingAs($dosen)
            ->post(route('participant.applications.store'), [
                ...$this->validPayload($unit),
                'success_indicators' => $indicators,
            ])
            ->assertRedirect();

        $application = Application::where('participant_id', $dosen->participant->id)->firstOrFail();
        $this->assertSame($indicators, $application->normalizedSuccessIndicators());
    }

    public function test_mentor_list_links_to_signature_page_without_reject(): void
    {
        $this->seed();
        $application = $this->waitingMentorApplication();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();

        $this->actingAs($mentor)
            ->get(route('mentor.applications'))
            ->assertOk()
            ->assertSee('Tinjau', false)
            ->assertSee('tandatangani', false)
            ->assertDontSee('name="decision" value="rejected"', false);

        $this->actingAs($mentor)
            ->get(route('mentor.applications.show', $application))
            ->assertOk()
            ->assertSee('Tinjau & tandatangani surat', false)
            ->assertSee('Minta revisi ke dosen')
            ->assertSee('Setujui')
            ->assertSee('tandatangani')
            ->assertSee('Tanda tangan mentor')
            ->assertSee('Komentar untuk dosen')
            ->assertDontSee('name="decision" value="rejected"', false);
    }

    public function test_mentor_revision_returns_to_dosen_then_back_to_mentor_for_signature(): void
    {
        $this->seed();
        Notification::fake();

        $application = $this->waitingMentorApplication();
        $dosen = $application->participant->user;
        $mentor = Mentor::whereHas('user', fn ($q) => $q->where('email', 'mentor@imersi.id'))->firstOrFail();
        $unit = $application->businessUnit;

        $this->actingAs($mentor->user)
            ->from(route('mentor.applications.show', $application))
            ->post(route('mentor.applications.review', $application), [
                'decision' => 'revision',
            ])
            ->assertRedirect(route('mentor.applications.show', $application))
            ->assertSessionHasErrors('mentor_note');

        $this->actingAs($mentor->user)
            ->post(route('mentor.applications.review', $application), [
                'decision' => 'revision',
                'mentor_note' => 'Indikator belum terukur, perjelas target minggu ke-4.',
            ])
            ->assertRedirect(route('mentor.applications'));

        $this->assertSame('revision', $application->fresh()->status);
        Notification::assertSentTo($dosen, ImersiAlert::class);

        $this->actingAs($dosen)
            ->get(route('participant.applications.edit', $application))
            ->assertOk()
            ->assertSee('Perbaiki form pendaftaran');

        $this->actingAs($dosen)
            ->put(route('participant.applications.update', $application), $this->validPayload($unit))
            ->assertRedirect(route('participant.applications.show', $application));

        $application->refresh();
        $this->assertSame('waiting_mentor', $application->status);
        Notification::assertSentTo($mentor->user, ImersiAlert::class);

        $this->actingAs($mentor->user)
            ->get(route('mentor.applications.show', $application))
            ->assertOk()
            ->assertSee('Tanda tangan mentor');

        $this->actingAs($mentor->user)
            ->post(route('mentor.applications.review', $application), [
                'decision' => 'revision',
                'mentor_note' => 'Masih kurang detail pada indikator kedua, mohon diperbaiki lagi.',
            ])
            ->assertRedirect(route('mentor.applications'));

        $this->assertSame('revision', $application->fresh()->status);
        $this->assertSame('Masih kurang detail pada indikator kedua, mohon diperbaiki lagi.', $application->fresh()->revision_note);
    }

    public function test_mentor_cannot_approve_without_signature_and_can_approve_with_signature(): void
    {
        $this->seed();
        $application = $this->waitingMentorApplication();
        $mentor = Mentor::whereHas('user', fn ($q) => $q->where('email', 'mentor@imersi.id'))->firstOrFail();

        $this->actingAs($mentor->user)
            ->from(route('mentor.applications.show', $application))
            ->post(route('mentor.applications.review', $application), [
                'decision' => 'approved',
            ])
            ->assertRedirect(route('mentor.applications.show', $application))
            ->assertSessionHasErrors('mentor_signature');

        $this->actingAs($mentor->user)
            ->post(route('mentor.applications.review', $application), [
                'decision' => 'approved',
                'mentor_signature' => $this->sampleSignature(),
            ])
            ->assertRedirect(route('mentor.applications'));

        $application->refresh();
        $this->assertSame('waiting_admin', $application->status);
        $this->assertNotNull($application->mentor_signature);
        $this->assertNotNull($application->mentor_signed_at);
    }

    public function test_approval_letter_shows_only_dosen_and_mentor_signature_blocks(): void
    {
        $this->seed();
        $application = $this->waitingMentorApplication();

        $this->actingAs(User::where('email', 'mentor@imersi.id')->firstOrFail())
            ->get(route('mentor.applications.show', $application))
            ->assertOk()
            ->assertSee('Dosen')
            ->assertSee('Mentor')
            ->assertDontSee('Admin final')
            ->assertDontSee('Pengelola program');
    }

    public function test_admin_matching_hides_reject_button(): void
    {
        $this->seed();
        $application = $this->waitingMentorApplication();
        $admin = User::where('email', 'admin@imersi.id')->firstOrFail();

        // Put back to submitted so admin matching form is visible.
        $application->update(['status' => 'submitted']);

        $this->actingAs($admin)
            ->get(route('admin.matching'))
            ->assertOk()
            ->assertSee('Teruskan ke mentor')
            ->assertSee('Revisi')
            ->assertDontSee('>Tolak</button>', false)
            ->assertDontSee('value="rejected"', false);
    }

    /**
     * @return array{business_unit_id: int, shared_goal: string, activity_types: list<string>, problem_statement: string, main_output: string, participant_benefit: string, business_benefit: string, success_indicators: list<string>, participant_signature: string, period_start: string, period_end: string, declaration: string}
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
            'participant_signature' => $this->sampleSignature(),
            'period_start' => '2026-09-10',
            'period_end' => '2026-11-10',
            'declaration' => '1',
        ];
    }

    private function sampleSignature(): string
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';
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

    private function waitingMentorApplication(): Application
    {
        $dosen = $this->newDosen();
        $unit = BusinessUnit::where('name', 'Digital Business')->firstOrFail();
        $mentor = Mentor::whereHas('user', fn ($q) => $q->where('email', 'mentor@imersi.id'))->firstOrFail();

        $this->actingAs($dosen)->post(route('participant.applications.store'), $this->validPayload($unit));
        $application = Application::where('participant_id', $dosen->participant->id)->firstOrFail();

        $this->actingAs(User::where('email', 'admin@imersi.id')->firstOrFail())
            ->post(route('admin.matching.update', $application), [
                'status' => 'approved',
                'mentor_id' => $mentor->id,
                'business_unit_id' => $application->business_unit_id,
            ]);

        return $application->fresh(['participant.user', 'businessUnit', 'mentor.user']);
    }
}
