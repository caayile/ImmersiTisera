<?php

namespace App\Services;

use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\Mentor;
use App\Models\Participant;
use App\Models\Program;
use App\Models\User;
use App\Notifications\ImersiAlert;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationApprovalService
{
    public function __construct(private MatchingService $matching) {}

    /**
     * @param  array{motivation: string, learning_objectives: string, planned_activities: string, expected_output: string, campus_benefit: string, period_start: string, period_end: string, cv_path: string}  $data
     */
    public function submit(Participant $participant, BusinessUnit $unit, array $data): Application
    {
        abort_unless($participant->study_program, 422, 'Lengkapi profil terlebih dahulu.');

        $open = Application::query()
            ->where('participant_id', $participant->id)
            ->whereIn('status', ['submitted', 'waiting_mentor', 'waiting_admin'])
            ->exists();

        if ($open) {
            throw ValidationException::withMessages([
                'business_unit_id' => 'Anda masih memiliki pendaftaran yang sedang ditinjau.',
            ]);
        }

        $match = $this->matching->score($participant, $unit);
        $mentor = $unit->mentors()->first();
        $period = $this->periodPayload($data);

        $application = Application::create([
            'participant_id' => $participant->id,
            'department_id' => $unit->department_id,
            'business_unit_id' => $unit->id,
            'mentor_id' => $mentor?->id,
            ...$this->questionnairePayload($data),
            ...$period,
            'cv_path' => $data['cv_path'],
            'match_score' => $match['score'],
            'relevance_warning' => $match['warning'],
            'status' => 'submitted',
        ]);

        $application->update([
            'letter_number' => $application->generateLetterNumber(),
        ]);

        $this->notifyAdmins(
            'Pendaftaran baru',
            $participant->user->name.' mengajukan program di '.$unit->name.'.',
            route('admin.matching')
        );

        if ($mentor?->user) {
            $mentor->user->notify(new ImersiAlert(
                'Pendaftaran masuk antrean',
                $participant->user->name.' mengajukan '.$unit->name.'. Menunggu tinjauan admin terlebih dahulu.',
                route('mentor.applications')
            ));
        }

        return $application->fresh(['participant.user', 'department', 'businessUnit', 'mentor.user']);
    }

    /**
     * @param  array{motivation: string, learning_objectives: string, planned_activities: string, expected_output: string, campus_benefit: string, period_start: string, period_end: string, business_unit_id: int, cv_path?: string}  $data
     */
    public function resubmit(Application $application, array $data): Application
    {
        abort_unless($application->status === 'revision', 403);

        $unit = BusinessUnit::with('mentors')->findOrFail($data['business_unit_id']);
        $match = $this->matching->score($application->participant, $unit);

        $application->update([
            'department_id' => $unit->department_id,
            'business_unit_id' => $unit->id,
            'mentor_id' => $application->mentor_id ?: $unit->mentors()->first()?->id,
            ...$this->questionnairePayload($data),
            ...$this->periodPayload($data),
            ...isset($data['cv_path']) ? ['cv_path' => $data['cv_path']] : [],
            'match_score' => $match['score'],
            'relevance_warning' => $match['warning'],
            'revision_note' => null,
            'status' => 'submitted',
        ]);

        $this->notifyAdmins(
            'Pendaftaran dikirim ulang',
            $application->participant->user->name.' memperbaiki pendaftaran '.$unit->name.'.',
            route('admin.matching')
        );

        return $application->fresh(['participant.user', 'department', 'businessUnit', 'mentor.user']);
    }

    /**
     * @param  array{status: string, mentor_id?: int|null, business_unit_id?: int|null, matching_notes?: string|null, revision_note?: string|null}  $data
     */
    public function adminReview(Application $application, array $data): Application
    {
        abort_unless(in_array($application->status, ['submitted', 'waiting_admin'], true), 422, 'Pendaftaran ini tidak menunggu tinjauan admin.');

        if ($application->status === 'submitted') {
            return $this->adminFirstReview($application, $data);
        }

        return $this->adminFinalReview($application, $data);
    }

    /**
     * @param  array{decision: string, mentor_note?: string|null, revision_note?: string|null}  $data
     */
    public function mentorReview(Application $application, Mentor $mentor, array $data): Application
    {
        abort_unless($application->mentor_id === $mentor->id, 403);
        abort_unless($application->status === 'waiting_mentor', 422, 'Pendaftaran ini tidak menunggu tinjauan mentor.');

        if ($data['decision'] === 'revision') {
            $application->update([
                'status' => 'revision',
                'mentor_note' => $data['mentor_note'] ?? $application->mentor_note,
                'revision_note' => $data['revision_note'] ?? $data['mentor_note'] ?? $application->revision_note,
                'mentor_reviewed_at' => now(),
            ]);
            $application->participant->user->notify(new ImersiAlert(
                'Pendaftaran perlu revisi',
                $application->revision_note ?: 'Mentor meminta perbaikan surat persetujuan.',
                route('participant.applications.show', $application)
            ));

            return $application->fresh(['participant.user', 'department', 'businessUnit', 'mentor.user']);
        }

        if ($data['decision'] === 'rejected') {
            $application->update([
                'status' => 'rejected',
                'mentor_note' => $data['mentor_note'] ?? $application->mentor_note,
                'mentor_reviewed_at' => now(),
            ]);
            $application->participant->user->notify(new ImersiAlert(
                'Pendaftaran ditolak mentor',
                $application->mentor_note ?: 'Mentor menolak pendaftaran program.',
                route('participant.applications.show', $application)
            ));

            return $application->fresh(['participant.user', 'department', 'businessUnit', 'mentor.user']);
        }

        $application->update([
            'status' => 'waiting_admin',
            'mentor_note' => $data['mentor_note'] ?? $application->mentor_note,
            'mentor_reviewed_at' => now(),
        ]);

        $this->notifyAdmins(
            'Menunggu pengesahan admin',
            'Mentor menyetujui pendaftaran '.$application->participant->user->name.'.',
            route('admin.matching')
        );
        $application->participant->user->notify(new ImersiAlert(
            'Disetujui mentor',
            'Surat persetujuan sudah disetujui mentor dan menunggu pengesahan admin.',
            route('participant.applications.show', $application)
        ));

        return $application->fresh(['participant.user', 'department', 'businessUnit', 'mentor.user']);
    }

    /**
     * @param  array{status: string, mentor_id?: int|null, business_unit_id?: int|null, matching_notes?: string|null, revision_note?: string|null}  $data
     */
    private function adminFirstReview(Application $application, array $data): Application
    {
        if (($data['business_unit_id'] ?? null) !== null) {
            $unit = BusinessUnit::findOrFail($data['business_unit_id']);
            $application->business_unit_id = $unit->id;
            $application->department_id = $unit->department_id;
        }

        if (($data['mentor_id'] ?? null) !== null) {
            $application->mentor_id = $data['mentor_id'];
        }

        $application->matching_notes = $data['matching_notes'] ?? $application->matching_notes;
        $application->admin_reviewed_at = now();

        if ($data['status'] === 'revision') {
            $application->status = 'revision';
            $application->revision_note = $data['revision_note'] ?? $data['matching_notes'] ?? $application->revision_note;
            $application->save();
            $application->participant->user->notify(new ImersiAlert(
                'Pendaftaran perlu revisi',
                $application->revision_note ?: 'Admin meminta perbaikan form pendaftaran.',
                route('participant.applications.show', $application)
            ));

            return $application->fresh(['participant.user', 'department', 'businessUnit', 'mentor.user']);
        }

        if ($data['status'] === 'rejected') {
            $application->status = 'rejected';
            $application->save();
            $application->participant->user->notify(new ImersiAlert(
                'Pendaftaran ditolak',
                $application->matching_notes ?: 'Admin menolak pendaftaran program.',
                route('participant.applications.show', $application)
            ));

            return $application->fresh(['participant.user', 'department', 'businessUnit', 'mentor.user']);
        }

        if (! $application->mentor_id) {
            throw ValidationException::withMessages([
                'mentor_id' => 'Pilih mentor sebelum meneruskan ke tinjauan mentor.',
            ]);
        }

        $application->status = 'waiting_mentor';
        $application->save();

        $application->load(['mentor.user', 'participant.user', 'businessUnit']);
        $application->mentor?->user?->notify(new ImersiAlert(
            'Pendaftaran menunggu persetujuan',
            $application->participant->user->name.' mengajukan '.$application->businessUnit->name.'.',
            route('mentor.applications')
        ));
        $application->participant->user->notify(new ImersiAlert(
            'Diteruskan ke mentor',
            'Admin sudah meninjau pendaftaran. Menunggu persetujuan mentor.',
            route('participant.applications.show', $application)
        ));

        return $application;
    }

    /**
     * @param  array{status: string, matching_notes?: string|null, revision_note?: string|null}  $data
     */
    private function adminFinalReview(Application $application, array $data): Application
    {
        $application->matching_notes = $data['matching_notes'] ?? $application->matching_notes;
        $application->admin_finalized_at = now();

        if ($data['status'] === 'revision') {
            $application->status = 'revision';
            $application->revision_note = $data['revision_note'] ?? $data['matching_notes'] ?? $application->revision_note;
            $application->save();
            $application->participant->user->notify(new ImersiAlert(
                'Pendaftaran perlu revisi',
                $application->revision_note ?: 'Admin meminta perbaikan sebelum pengesahan.',
                route('participant.applications.show', $application)
            ));

            return $application->fresh(['participant.user', 'department', 'businessUnit', 'mentor.user']);
        }

        if ($data['status'] === 'rejected') {
            $application->status = 'rejected';
            $application->save();
            $application->participant->user->notify(new ImersiAlert(
                'Pendaftaran ditolak',
                $application->matching_notes ?: 'Admin menolak pengesahan akhir.',
                route('participant.applications.show', $application)
            ));

            return $application->fresh(['participant.user', 'department', 'businessUnit', 'mentor.user']);
        }

        return DB::transaction(function () use ($application) {
            $application->status = 'approved';
            $application->save();

            $program = Program::firstOrCreate(
                ['application_id' => $application->id],
                [
                    'participant_id' => $application->participant_id,
                    'mentor_id' => $application->mentor_id,
                    'department_id' => $application->department_id,
                    'business_unit_id' => $application->business_unit_id,
                    'status' => 'submitted',
                ]
            );
            $program->agreement()->firstOrCreate(['program_id' => $program->id], ['status' => 'draft']);

            $application->participant->user->notify(new ImersiAlert(
                'Pendaftaran disetujui',
                'Surat persetujuan disahkan. Lanjutkan ke Perjanjian Imersi Industri.',
                route('participant.agreement')
            ));

            return $application->fresh(['participant.user', 'department', 'businessUnit', 'mentor.user', 'program']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, string>
     */
    private function questionnairePayload(array $data): array
    {
        return collect(Application::registrationQuestionKeys())
            ->mapWithKeys(fn (string $key): array => [$key => $data[$key]])
            ->all();
    }

    /**
     * @param  array{period_start: string, period_end: string}  $data
     * @return array{period_start: string, period_end: string, preferred_period: string}
     */
    private function periodPayload(array $data): array
    {
        $start = Carbon::parse($data['period_start'])->startOfDay();
        $end = Carbon::parse($data['period_end'])->startOfDay();

        return [
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'preferred_period' => $start->format('d M Y').' – '.$end->format('d M Y'),
        ];
    }

    private function notifyAdmins(string $title, string $message, string $url): void
    {
        User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->get()
            ->each(fn (User $admin) => $admin->notify(new ImersiAlert($title, $message, $url)));
    }
}
