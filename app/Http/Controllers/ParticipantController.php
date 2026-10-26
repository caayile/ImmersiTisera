<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\Certificate;
use App\Models\CollaborationPipeline;
use App\Models\Department;
use App\Models\Evaluation;
use App\Models\EvaluationQuestion;
use App\Models\EvaluationScale;
use App\Models\EvaluationSection;
use App\Models\Logbook;
use App\Models\MentorSession;
use App\Models\Participant;
use App\Models\Program;
use App\Models\ProgramOutput;
use App\Models\Timeline;
use App\Notifications\ImersiAlert;
use App\Services\AgreementLetterService;
use App\Services\ApplicationApprovalService;
use App\Services\AvatarStorageService;
use App\Services\CertificateIssuer;
use App\Services\CertificatePdfRenderer;
use App\Services\CertificateSignatureStorage;
use App\Support\Status;
use App\Support\StudyPrograms;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ParticipantController extends Controller
{
    public function dashboard(Request $request)
    {
        $program = $this->currentProgram($request);
        $program?->refreshProgress();
        $application = Application::with(['department', 'businessUnit', 'mentor.user'])
            ->where('participant_id', $request->user()->participant?->id)
            ->latest()
            ->first();

        $participantId = $request->user()->participant?->id;

        return view('participant.dashboard', [
            'participant' => $request->user()->participant,
            'program' => $program?->fresh(['department', 'businessUnit', 'mentor.user', 'mentor.department', 'mentor.businessUnit', 'agreement', 'logbooks', 'timelines', 'mentorSessions', 'outputs', 'evaluations', 'collaboration']),
            'notifications' => $request->user()->unreadNotifications()->latest()->take(5)->get(),
            'application' => $application,
            'stats' => [
                'programs' => Program::where('participant_id', $participantId)->count(),
                'logbooks' => Logbook::where('participant_id', $participantId)->count(),
                'mentorings' => MentorSession::where('participant_id', $participantId)->count(),
                'outputs' => ProgramOutput::where('participant_id', $participantId)->where('is_final_report', false)->count(),
            ],
        ]);
    }

    public function profile(Request $request)
    {
        $participant = $request->user()->participant ?? Participant::create(['user_id' => $request->user()->id]);

        return view('participant.profile', [
            'participant' => $participant,
            'studyProgramCatalog' => StudyPrograms::catalog(),
            'placementCatalog' => collect(StudyPrograms::allPrograms())
                ->mapWithKeys(fn (string $program) => [$program => StudyPrograms::placementTargets($program)])
                ->all(),
        ]);
    }

    public function updateProfile(Request $request, AvatarStorageService $avatars)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'institution' => ['nullable', 'string', 'max:180'],
            'avatar' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'nidn' => ['nullable', 'string', 'max:40'],
            'faculty' => ['required', 'string', Rule::in(StudyPrograms::faculties())],
            'study_program' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if (! StudyPrograms::isValid((string) $request->input('faculty'), (string) $value)) {
                        $fail('Program studi tidak valid untuk fakultas yang dipilih.');
                    }
                },
            ],
            'expertise' => ['required', 'string'],
            'competency' => ['required', 'string'],
            'experience' => ['nullable', 'string'],
            'motivation' => ['nullable', 'string'],
        ]);

        $request->user()->update([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'avatar' => $request->hasFile('avatar') ? $avatars->store($request->file('avatar'), $request->user()->avatar) : $request->user()->avatar,
        ]);

        $profileData = $request->user()->participant?->profile_data ?? [];
        $institution = trim((string) ($data['institution'] ?? ''));
        if ($institution === '') {
            unset($profileData['institution']);
        } else {
            $profileData['institution'] = $institution;
        }

        $request->user()->participant()->updateOrCreate(['user_id' => $request->user()->id], [
            'nidn' => $data['nidn'] ?? null,
            'faculty' => $data['faculty'],
            'study_program' => $data['study_program'],
            'expertise' => $this->csv($data['expertise']),
            'competency' => $this->csv($data['competency']),
            'experience' => $data['experience'] ?? null,
            'motivation' => $data['motivation'] ?? null,
            'profile_data' => $profileData === [] ? null : $profileData,
        ]);

        return back()->with('status', 'Profil disimpan.');
    }

    public function applications(Request $request)
    {
        $apps = Application::with(['department', 'businessUnit', 'mentor.user'])
            ->where('participant_id', $request->user()->participant?->id)
            ->latest()
            ->get();

        return view('participant.applications', compact('apps'));
    }

    public function createApplication(Request $request)
    {
        $participant = $request->user()->participant;
        if (! $participant?->study_program) {
            return redirect()->route('participant.profile')->with('status', 'Lengkapi profil sebelum mendaftar program.');
        }

        $unit = BusinessUnit::with(['department', 'mentors.user'])->find($request->integer('unit'));
        if (! $unit || ! $unit->isOpen()) {
            return redirect()->route('departments.index')->with('status', 'Pilih unit bisnis atau departemen terlebih dahulu.');
        }

        $alreadyApplied = $participant->applications()->where('business_unit_id', $unit->id)->exists();
        if (! $alreadyApplied && $unit->isFull()) {
            return redirect()->route('departments.index')->with('status', 'Kuota lowongan ini sudah penuh (2 pendaftar). Silakan pilih lowongan lain.');
        }

        return view('participant.application-form', [
            'participant' => $participant,
            'unit' => $unit,
            'application' => null,
            'periodStart' => now()->toDateString(),
            'periodEnd' => Application::periodEndFromStart(now())->toDateString(),
        ]);
    }

    public function storeApplication(Request $request, ApplicationApprovalService $approvals)
    {
        $data = $request->validate([
            'business_unit_id' => ['required', 'exists:business_units,id'],
            ...$this->registrationFieldRules($request),
        ]);

        $participant = $request->user()->participant;
        if (! $participant?->study_program) {
            return redirect()->route('participant.profile')->with('status', 'Lengkapi profil sebelum mendaftar program.');
        }

        $unit = BusinessUnit::with('mentors')->findOrFail($data['business_unit_id']);
        if (! $unit->isOpen()) {
            return redirect()->route('departments.index')->with('status', 'Lowongan departemen ini sudah ditutup.');
        }

        try {
            $application = $approvals->submit($participant, $unit, $data);
        } catch (ValidationException $exception) {
            return redirect()->back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('participant.applications.show', $application)
            ->with('status', 'Pendaftaran dan surat persetujuan terkirim ke admin.');
    }

    public function showApplication(Request $request, Application $application)
    {
        $this->authorizeApplication($request, $application);

        return view('participant.application-show', [
            'application' => $application->load(['participant.user', 'department', 'businessUnit', 'mentor.user', 'program']),
        ]);
    }

    public function editApplication(Request $request, Application $application)
    {
        $this->authorizeApplication($request, $application);

        $canEdit = $application->canBeRevisedByParticipant();
        $start = $application->period_start ?? now();

        return view('participant.application-form', [
            'participant' => $request->user()->participant,
            'unit' => $application->businessUnit()->with('department')->first(),
            'application' => $application,
            'readOnly' => ! $canEdit,
            'periodStart' => $start->toDateString(),
            'periodEnd' => ($application->period_end ?? Application::periodEndFromStart($start))->toDateString(),
        ]);
    }

    public function updateApplication(Request $request, Application $application, ApplicationApprovalService $approvals)
    {
        $this->authorizeApplication($request, $application);
        abort_unless($application->canBeRevisedByParticipant(), 403, 'Pendaftaran hanya dapat dikirim ulang saat status revisi.');

        $data = $request->validate($this->registrationFieldRules($request));
        $data['business_unit_id'] = $application->business_unit_id;

        $approvals->resubmit($application, $data);

        $fresh = $application->fresh();
        $message = $fresh->status === 'waiting_mentor'
            ? 'Perbaikan dikirim ke mentor untuk ditinjau dan ditandatangani.'
            : 'Pendaftaran dikirim ulang ke admin.';

        return redirect()
            ->route('participant.applications.show', $application)
            ->with('status', $message);
    }

    public function program(Request $request)
    {
        // Menu Program dilebur ke Beranda (dashboard). Pertahankan route lama
        // agar tautan lama/notifikasi tidak 404.
        return redirect()->route('participant.dashboard');
    }

    public function agreement(Request $request)
    {
        $program = $this->currentProgram($request);

        return view('participant.agreement', ['program' => $program, 'agreement' => $program?->agreement]);
    }

    public function printAgreement(Request $request)
    {
        $program = $this->currentProgram($request);
        abort_unless($program?->agreement?->status === 'agreed', 404);

        if (blank($program->agreement->letter_number)) {
            app(AgreementLetterService::class)->issue($program->agreement);
            $program->agreement->refresh();
        }

        return view('participant.agreement-print', [
            'program' => $program->load(['participant.user', 'mentor.user', 'department', 'businessUnit', 'agreement', 'application']),
            'agreement' => $program->agreement,
            'pdf' => false,
        ]);
    }

    public function downloadAgreementPdf(Request $request)
    {
        $program = $this->currentProgram($request);
        abort_unless($program?->agreement?->status === 'agreed', 404);

        if (blank($program->agreement->letter_number)) {
            app(AgreementLetterService::class)->issue($program->agreement);
        }

        $program->load(['participant.user', 'mentor.user', 'department', 'businessUnit', 'agreement', 'application']);
        $agreement = $program->agreement->fresh();

        $filename = 'Perjanjian-Magang-'.preg_replace('/[^A-Za-z0-9]+/', '-', (string) ($agreement->letter_number ?? 'tanpa-nomor')).'.pdf';

        return Pdf::loadView('participant.agreement-print', [
            'program' => $program,
            'agreement' => $agreement,
            'pdf' => true,
        ])->setPaper('a4', 'portrait')->download($filename);
    }

    public function updateAgreement(Request $request)
    {
        $program = $this->currentProgram($request, true);
        $agreement = $program->agreement;
        abort_unless($program && $agreement && in_array($agreement->status, ['draft', 'revision'], true), 403);

        $data = $request->validate([
            'objective' => ['required', 'string'],
            'problem_statement' => ['required', 'string'],
            'activities' => ['required', 'string'],
            'main_output' => ['required', 'string'],
            'participant_benefit' => ['required', 'string'],
            'business_benefit' => ['required', 'string'],
            'success_indicators' => ['required', 'array', 'max:3'],
            'participant_signature' => [
                'required',
                'string',
                'regex:/^data:image\/(png|jpeg|jpg|webp);base64,/i',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (strlen((string) $value) > 900_000) {
                        $fail('Tanda tangan dosen terlalu besar.');
                    }
                },
            ],
            'collaboration_potential' => ['nullable', 'string'],
        ]);

        $agreement->update([
            ...$data,
            'status' => 'submitted',
            'participant_approved_at' => now(),
            'mentor_approved_at' => null,
            'mentor_signature' => null,
        ]);
        $program->mentor->user->notify(new ImersiAlert('Perjanjian diajukan', 'Menunggu persetujuan mentor.', route('mentor.agreements')));

        return back()->with('status', 'Perjanjian diajukan ke mentor.');
    }

    public function signAgreement(Request $request)
    {
        $program = $this->currentProgram($request, true);
        $agreement = $program->agreement;
        abort_unless($program && $agreement && in_array($agreement->status, ['draft', 'revision'], true), 403);

        $data = $request->validate([
            'participant_signature' => [
                'required',
                'string',
                'regex:/^data:image\/(png|jpeg|jpg|webp);base64,/i',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (strlen((string) $value) > 900_000) {
                        $fail('Tanda tangan dosen terlalu besar.');
                    }
                },
            ],
        ]);

        $agreement->update([
            'participant_signature' => $data['participant_signature'],
            'participant_approved_at' => now(),
            'status' => 'submitted',
            'mentor_approved_at' => null,
            'mentor_signature' => null,
        ]);
        $program->mentor->user->notify(new ImersiAlert('Perjanjian diajukan', 'Menunggu persetujuan mentor.', route('mentor.agreements')));

        return back()->with('status', 'Surat perjanjian ditandatangani dan diajukan ke mentor.');
    }

    public function timeline(Request $request)
    {
        $program = $this->currentProgram($request);

        if ($program) {
            $program->seedTimeline();
            $program->refreshProgress();
        }

        return view('participant.timeline', [
            'program' => $program?->fresh([
                'department',
                'businessUnit',
                'timelines',
            ]),
        ]);
    }

    public function updateTimeline(Request $request, Timeline $timeline)
    {
        $participantId = $request->user()->participant?->id;
        abort_unless($timeline->program && (int) $timeline->program->participant_id === (int) $participantId, 403);
        abort_unless(in_array($timeline->week, Status::CHECKPOINT_WEEKS, true), 404);

        $timeline->program->refreshProgress();
        abort_unless($timeline->program->fresh()->isCheckpointOpen((int) $timeline->week), 403);
        abort_if(in_array($timeline->status, ['done', 'approved'], true), 403, 'Checkpoint sudah disetujui dan tidak dapat diubah.');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'expected_output' => ['nullable', 'string'],
            'evidence_link' => ['nullable', 'string', 'max:2048'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp'],
        ]);

        $previousAttachmentPath = $timeline->attachment_path;
        $attachmentPath = $request->file('attachment')?->store('timeline-reports', 'public');

        $payload = [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'evidence_link' => $data['evidence_link'] ?? null,
            'attachment_path' => $attachmentPath ?? $previousAttachmentPath,
            'status' => 'submitted',
        ];

        // expected_output hanya ditimpa jika form mengirimnya (popup baru tidak punya field ini).
        if (array_key_exists('expected_output', $data)) {
            $payload['expected_output'] = $data['expected_output'];
        }

        $timeline->update($payload);

        if ($attachmentPath && $previousAttachmentPath) {
            Storage::disk('public')->delete($previousAttachmentPath);
        }

        $timeline->program->mentor?->user?->notify(new ImersiAlert(
            'Checkpoint diajukan',
            'Minggu '.$timeline->week.' menunggu pengesahan.',
            route('mentor.timeline')
        ));

        return back()->with('status', 'Checkpoint minggu '.$timeline->week.' dikirim untuk disahkan mentor.');
    }

    public function logbooks(Request $request)
    {
        $program = $this->currentProgram($request);
        $participant = $request->user()->participant;
        $programs = $this->allPrograms($request);

        $entries = Logbook::query()
            ->with(['program.businessUnit', 'program.department'])
            ->where('participant_id', $participant?->id)
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $month = $this->historyMonth($request, $programs);
        $byDate = $entries->groupBy(fn (Logbook $log) => $log->date->toDateString());
        $formDate = $this->historyDay($request, 'date')?->toDateString() ?? today()->toDateString();
        $entryPrefill = $byDate->map(fn ($logs) => [
            'attendance' => $logs->last()->attendance ?? 'Hadir',
            'what_did' => (string) $logs->last()->what_i_did,
            'what_learned' => (string) $logs->last()->what_i_learned,
            'what_found' => (string) $logs->last()->what_i_found,
            'obstacles' => (string) ($logs->last()->value ?? ''),
            'output' => (string) ($logs->last()->next_action ?? ''),
        ]);

        return view('participant.logbooks', compact('program', 'month', 'byDate', 'formDate', 'entryPrefill'));
    }

    private function historyMonth(Request $request, $programs): Carbon
    {
        $fallback = today()->startOfMonth();

        try {
            $parsed = trim((string) $request->string('month'));

            return $parsed === '' ? $fallback : Carbon::createFromFormat('Y-m', $parsed)->startOfMonth();
        } catch (\Exception) {
            return $fallback;
        }
    }

    private function historyDay(Request $request, string $key = 'day'): ?Carbon
    {
        try {
            $parsed = trim((string) $request->string($key));

            return $parsed === '' ? null : Carbon::createFromFormat('Y-m-d', $parsed)->startOfDay();
        } catch (\Exception) {
            return null;
        }
    }

    public function storeLogbook(Request $request)
    {
        $program = $this->currentProgram($request, true);
        abort_unless($program && $program->status === 'active', 403);

        $data = $request->validate([
            'entry_date' => ['required', 'date'],
            'attendance' => ['required', 'in:Hadir,Izin,Sakit,Tanpa Keterangan'],
            'what_did' => ['required', 'string'],
            'what_learned' => ['required', 'string'],
            'what_found' => ['required', 'string'],
            'obstacles' => ['nullable', 'string'],
            'output' => ['nullable', 'string'],
        ]);

        // One entry per date: resubmitting updates the existing record
        // and sends it back for mentor review.
        $payload = [
            'participant_id' => $program->participant_id,
            'date' => $data['entry_date'],
            'activity' => 'Logbook harian',
            'attendance' => $data['attendance'],
            'what_i_did' => $data['what_did'],
            'what_i_learned' => $data['what_learned'],
            'what_i_found' => $data['what_found'],
            'value' => $data['obstacles'] ?? null,
            'next_action' => $data['output'] ?? null,
            'status' => 'submitted',
        ];

        $existing = $program->logbooks()->whereDate('date', $data['entry_date'])->latest('id')->first();

        if ($existing) {
            $existing->update($payload);
            $program->mentor->user->notify(new ImersiAlert('Logbook diperbarui', 'Ada perubahan logbook yang menunggu pemeriksaan.', route('mentor.logbooks')));

            return back()->with('status', 'Logbook diperbarui.');
        }

        $program->logbooks()->create($payload);
        $program->mentor->user->notify(new ImersiAlert('Logbook baru', 'Ada logbook yang menunggu pemeriksaan.', route('mentor.logbooks')));

        return back()->with('status', 'Logbook dikirim.');
    }

    public function mentoring(Request $request)
    {
        $program = $this->currentProgram($request);
        $sessions = $program
            ? $program->mentorSessions()->orderByDesc('session_date')->orderByDesc('id')->get()
            : collect();

        return view('participant.mentoring', compact('program', 'sessions'));
    }

    public function storeMentoring(Request $request)
    {
        $program = $this->currentProgram($request, true);
        abort_unless($program && $program->status === 'active', 403);

        $data = $request->validate([
            'session_date' => ['required', 'date'],
            'activity' => ['required', 'string', 'max:180'],
            'findings' => ['nullable', 'string'],
            'current_work' => ['nullable', 'string'],
            'next_action' => ['nullable', 'string'],
            'evidence_link' => ['nullable', 'string', 'max:2048'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png,webp'],
        ]);

        $sessionDate = Carbon::parse($data['session_date'])->startOfDay();
        $week = 1;
        if ($program->start_date) {
            $week = min(8, max(1, (int) ceil(($program->start_date->diffInDays($sessionDate) + 1) / 7)));
        }

        $program->mentorSessions()->create([
            'mentor_id' => $program->mentor_id,
            'participant_id' => $program->participant_id,
            'week' => $week,
            'session_date' => $sessionDate->toDateString(),
            'activity' => $data['activity'],
            'findings' => $data['findings'] ?? null,
            'current_work' => $data['current_work'] ?? null,
            'next_action' => $data['next_action'] ?? null,
            'evidence_link' => $data['evidence_link'] ?? null,
            'attachment_path' => $request->file('attachment')?->store('mentoring-docs', 'public'),
        ]);

        $program->mentor?->user?->notify(new ImersiAlert(
            'Sesi mentoring baru',
            $data['activity'].' · '.$sessionDate->format('d M Y'),
            route('mentor.mentoring')
        ));

        return back()->with('status', 'Sesi mentoring dikirim.');
    }

    public function outputs(Request $request)
    {
        // Menu Hasil dilebur: logbook + mentoring + checkpoint sudah cukup.
        // Laporan akhir tetap lewat menu Laporan Akhir.
        return redirect()->route('participant.final-report');
    }

    public function storeOutput(Request $request)
    {
        // Laporan akhir bisa diisi per siklus (baris tabel), bukan hanya siklus berjalan.
        $targetId = $request->integer('program_id');
        $program = $targetId
            ? Program::where('id', $targetId)->where('participant_id', $request->user()->participant?->id)->first()
            : $this->currentProgram($request);
        abort_unless($program, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'type' => ['required', 'string'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'description' => ['nullable', 'string'],
            'link' => ['nullable', 'string', 'max:2048'],
            'hasil_file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,ppt,pptx,zip'],
            'laporan_link' => ['nullable', 'string', 'max:2048'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'business_unit_id' => ['nullable', 'exists:business_units,id'],
            'year' => ['nullable', 'digits:4', 'integer', 'min:2000', 'max:2100'],
            'level' => ['nullable', 'integer', 'min:0', 'max:4'],
            'is_main_output' => ['sometimes', 'boolean'],
            'is_final_report' => ['sometimes', 'boolean'],
            'file' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,doc,docx,ppt,pptx,zip'],
        ]);

        // One source per artifact: link xor file.
        if (filled($data['link'] ?? null) && $request->hasFile('hasil_file')) {
            throw ValidationException::withMessages([
                'link' => 'Isi salah satu saja untuk Hasil: tautan atau file.',
            ]);
        }

        if ($request->hasFile('file') && filled($data['laporan_link'] ?? null)) {
            throw ValidationException::withMessages([
                'file' => 'Isi salah satu saja untuk Laporan: file atau tautan.',
            ]);
        }

        if ($request->boolean('is_main_output')) {
            $program->outputs()->update(['is_main_output' => false]);
        }

        $payload = [
            'participant_id' => $program->participant_id,
            'title' => $data['title'],
            'type' => $data['type'],
            'link' => $data['link'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'business_unit_id' => $data['business_unit_id'] ?? null,
            'year' => $data['year'] ?? null,
            'is_main_output' => $request->boolean('is_main_output'),
            'is_final_report' => $request->boolean('is_final_report'),
            'status' => 'submitted',
        ];

        // Only overwrite the description when the form actually sends it,
        // so removing the field from a form never wipes stored data.
        if (array_key_exists('description', $data)) {
            $payload['description'] = $data['description'];
        }

        if ($request->file('file')) {
            $payload['file_path'] = $request->file('file')->store('outputs', 'public');
            $payload['laporan_link'] = null;
        } elseif (array_key_exists('laporan_link', $data)) {
            $payload['laporan_link'] = $data['laporan_link'];
        }

        if ($request->file('hasil_file')) {
            $payload['hasil_file_path'] = $request->file('hasil_file')->store('outputs', 'public');
            $payload['link'] = null;
        }

        // One final report per cycle: resubmitting updates the existing
        // record instead of duplicating it.
        $existingReport = $request->boolean('is_final_report')
            ? $program->outputs()->where('is_final_report', true)->first()
            : null;

        if ($existingReport) {
            $this->deleteReplacedOutputFiles($existingReport, $payload);
            $output = tap($existingReport)->update($payload);
        } else {
            $output = $program->outputs()->create($payload);
        }

        if ($output->is_final_report && array_key_exists('level', $data) && $data['level'] !== null) {
            CollaborationPipeline::updateOrCreate(
                ['program_id' => $program->id],
                ['level' => (int) $data['level']]
            );
        }
        $program->mentor->user->notify(new ImersiAlert('Hasil dikirim', $data['title'].' menunggu validasi.', route('mentor.participants.show', $program)));

        return back()->with('status', 'Hasil dikirim.');
    }

    /**
     * Remove stored files replaced by a new upload or a link, so each
     * artifact keeps exactly one source.
     *
     * @param  array<string, mixed>  $payload
     */
    private function deleteReplacedOutputFiles(ProgramOutput $output, array $payload): void
    {
        foreach (['file_path', 'hasil_file_path'] as $column) {
            if (array_key_exists($column, $payload) && $output->{$column} && $output->{$column} !== $payload[$column]) {
                Storage::disk('public')->delete($output->{$column});
            }
        }
    }

    public function evaluation(Request $request, CertificateIssuer $certificateIssuer)
    {
        $program = $this->currentProgram($request);
        if ($program) {
            $certificateIssuer->issueIfReady($program);
        }
        $mine = $program?->evaluations()->where('evaluator_id', $request->user()->id)->first();

        $sections = EvaluationSection::with(['questions' => fn ($query) => $query->where('is_active', true)->orderBy('position')])
            ->orderBy('position')
            ->get();

        $bank = $sections->isNotEmpty()
            ? $sections->map(fn (EvaluationSection $section) => [
                'key' => $section->key,
                'title' => $section->title,
                'questions' => $section->questions->pluck('question')->all(),
            ])->all()
            : EvaluationQuestion::defaultBank();

        $scales = EvaluationScale::orderBy('position')->get();
        $scaleList = $scales->isNotEmpty()
            ? $scales->map(fn (EvaluationScale $scale) => ['label' => $scale->label, 'value' => $scale->value])->all()
            : EvaluationScale::defaultScales();
        $scaleMax = collect($scaleList)->max('value') ?? 5;
        $certificates = $this->participantCertificates($request);
        // Kartu atas = siklus terbaru (aktif atau selesai) agar nilai & sertifikat tetap terlihat.
        $topCycle = $program;
        // Riwayat memuat semua siklus kecuali yang tampil di kartu atas, dari terbaru.
        $history = $program
            ? $this->allPrograms($request)->reject(fn ($cycle) => $topCycle && $cycle->id === $topCycle->id)->values()
            : collect();

        return view('participant.evaluation', compact('program', 'mine', 'bank', 'scaleList', 'scaleMax', 'certificates', 'history', 'topCycle'));
    }

    public function storeEvaluation(Request $request)
    {
        $program = $this->currentProgram($request);
        abort_unless($program, 404);
        $data = $this->evalRules($request);
        Evaluation::updateOrCreate(
            ['program_id' => $program->id, 'evaluator_id' => $request->user()->id],
            [
                'criteria' => array_map(fn (array $criterion) => [
                    'label' => trim($criterion['label']),
                    'score' => (int) $criterion['score'],
                ], $data['criteria']),
                'comments' => $data['comments'] ?? null,
            ]
        );

        return redirect(route('participant.evaluation').'#hasil')->with('status', 'Kuesioner tersimpan. Nilai dan sertifikat sudah dapat dibuka.');
    }

    public function report(Request $request, ?Program $program = null)
    {
        $current = $this->currentProgram($request);
        abort_unless($current, 404);

        $target = $program ?? $current;
        abort_unless((int) $target->participant_id === (int) $request->user()->participant?->id, 404);
        $target->loadMissing(['participant.user', 'mentor.user', 'department', 'businessUnit', 'evaluations.evaluator']);

        $mine = $target->evaluations->where('evaluator_id', $request->user()->id)->first();
        $report = $target->evaluations->whereNotIn('evaluator_id', [$request->user()->id])->first(fn ($eval) => $eval->hasReport());
        abort_unless($report, 404);

        // Kuesioner wajib hanya untuk siklus berjalan; siklus lama bersifat arsip.
        if ($target->id === $current->id) {
            abort_unless($mine, 404);
        }

        $program = $target;

        $ownCriteria = collect($mine?->criteriaForDisplay() ?? []);
        $scales = EvaluationScale::orderBy('position')->get();
        $scaleMax = $scales->isNotEmpty() ? $scales->max('value') : 5;

        return view('participant.evaluation-report', compact('program', 'mine', 'report', 'ownCriteria', 'scaleMax'));
    }

    public function printReport(Request $request, CertificateSignatureStorage $signatureStorage, ?Program $program = null)
    {
        [$target, $report] = $this->resolveReportForPrint($request, $program);

        return view('participant.evaluation-report-print', [
            'program' => $target,
            'report' => $report,
            'pdf' => false,
            'mentorSignature' => $signatureStorage->forDisplay($target->mentor?->certificate_signature),
        ]);
    }

    public function downloadReportPdf(Request $request, CertificateSignatureStorage $signatureStorage, ?Program $program = null)
    {
        [$target, $report] = $this->resolveReportForPrint($request, $program);

        $filename = 'Rapor-Magang-'.preg_replace('/[^A-Za-z0-9]+/', '-', (string) $target->participant->user->name).'.pdf';

        return Pdf::loadView('participant.evaluation-report-print', [
            'program' => $target,
            'report' => $report,
            'pdf' => true,
            'mentorSignature' => $signatureStorage->forDisplay($target->mentor?->certificate_signature),
        ])->setPaper('a4', 'portrait')->download($filename);
    }

    /**
     * @return array{0: Program, 1: Evaluation}
     */
    private function resolveReportForPrint(Request $request, ?Program $program = null): array
    {
        $current = $this->currentProgram($request);
        abort_unless($current, 404);

        $target = $program ?? $current;
        abort_unless((int) $target->participant_id === (int) $request->user()->participant?->id, 404);
        $target->loadMissing(['participant.user', 'mentor.user', 'department', 'businessUnit', 'evaluations.evaluator']);

        $mine = $target->evaluations->where('evaluator_id', $request->user()->id)->first();
        $report = $target->evaluations->whereNotIn('evaluator_id', [$request->user()->id])->first(fn ($eval) => $eval->hasReport());
        abort_unless($report, 404);

        // Kuesioner wajib hanya untuk siklus berjalan; siklus lama bersifat arsip.
        if ($target->id === $current->id) {
            abort_unless($mine, 404);
        }

        return [$target, $report];
    }

    public function finalReport(Request $request)
    {
        $programs = $this->allPrograms($request);
        $programs->loadMissing(['outputs.department', 'outputs.businessUnit']);

        return view('participant.final-report', [
            'program' => $this->currentProgram($request),
            'programs' => $programs,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'businessUnits' => BusinessUnit::with('department:id,name')->orderBy('name')->get(['id', 'name', 'department_id']),
            'reportFormData' => $programs->mapWithKeys(function (Program $cycle) {
                $report = $cycle->outputs->firstWhere('is_final_report', true);

                return [(string) $cycle->id => [
                    'exists' => (bool) $report,
                    'title' => $report?->title ?? '',
                    'year' => $report?->year ?? $cycle->end_date?->format('Y') ?? $cycle->start_date?->format('Y') ?? '',
                    'dept' => (string) ($report?->department_id ?? $cycle->department_id ?? ''),
                    'unit' => (string) ($report?->business_unit_id ?? $cycle->business_unit_id ?? ''),
                    'level' => (string) ($cycle->collaboration?->level ?? ''),
                    'link' => $report?->link ?? '',
                    'lapLink' => $report?->laporan_link ?? '',
                    'hasilNote' => $report?->hasil_file_path ? 'Sudah ada file (pilih baru untuk mengganti)' : 'Pilih File Dokumen',
                    'lapNote' => $report?->file_path ? 'Sudah ada file (pilih baru untuk mengganti)' : 'Pilih File Dokumen',
                ]];
            }),
        ]);
    }

    public function collaboration(Request $request)
    {
        $program = $this->currentProgram($request);

        return view('participant.collaboration', compact('program'));
    }

    public function certificates(Request $request)
    {
        // Sertifikat digabung ke halaman Hasil Nilai Magang (kuesioner → nilai + sertif).
        return redirect()->to(route('participant.evaluation').'#sertifikat');
    }

    /**
     * Sertifikat terbit milik peserta, dipakai menu Hasil Studi.
     *
     * @return Collection<int, Certificate>
     */
    private function participantCertificates(Request $request): Collection
    {
        $userId = (int) $request->user()->id;
        $currentProgramId = $this->currentProgram($request)?->id;

        return Certificate::query()
            ->with(['program.businessUnit', 'program.department', 'program.mentor.user', 'program.participant.user', 'program.evaluations'])
            ->whereHas('program', fn ($query) => $query->where('participant_id', $request->user()->participant?->id))
            ->where('status', 'issued')
            ->latest('issued_at')
            ->get()
            ->filter(fn (Certificate $certificate) => $this->participantCanViewCertificate($certificate->program, $userId, $currentProgramId))
            ->values();
    }

    public function printCertificate(Request $request, Certificate $certificate, CertificateSignatureStorage $signatureStorage)
    {
        $certificate->load(['program.businessUnit', 'program.department', 'program.mentor.user', 'program.participant.user', 'program.evaluations']);

        Gate::authorize('view', $certificate);

        return view('certificate.show', $this->certificateViewData($certificate, $signatureStorage));
    }

    public function downloadCertificatePdf(
        Request $request,
        Certificate $certificate,
        CertificateSignatureStorage $signatureStorage,
        CertificatePdfRenderer $pdfRenderer,
    ) {
        $certificate->load(['program.businessUnit', 'program.department', 'program.mentor.user', 'program.participant.user', 'program.evaluations']);
        Gate::authorize('view', $certificate);

        $pdf = $pdfRenderer->render(view('certificate.show', $this->certificateViewData($certificate, $signatureStorage))->render());

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="sertifikat-'.$certificate->global_sequence.'.pdf"',
        ]);
    }

    private function certificateViewData(Certificate $certificate, CertificateSignatureStorage $signatureStorage): array
    {
        return [
            'certificate' => $certificate,
            'signatureSource' => $signatureStorage->forCertificate(
                $certificate->mentor_signature_path ?: $certificate->mentor_signature,
                $certificate->program?->mentor?->certificate_signature,
            ),
            'signatorySignatureSource' => $signatureStorage->toDataUri(data_get($certificate->signatory_snapshot, 'signature_path')),
        ];
    }

    /**
     * @return array{title: string, message: string, action_url: string|null, action_label: string|null}|null
     */
    private function certificatePendingNotice(Request $request, ?Program $program, Collection $visibleCertificates): ?array
    {
        if (! $program) {
            return null;
        }

        $userId = (int) $request->user()->id;
        $issued = Certificate::query()
            ->where('program_id', $program->id)
            ->where('status', 'issued')
            ->exists();

        if ($visibleCertificates->contains(fn (Certificate $certificate) => (int) $certificate->program_id === (int) $program->id)) {
            return null;
        }

        $mentorReport = $this->mentorReportFor($program, $userId);

        if (! $mentorReport) {
            return [
                'title' => 'Sertifikat belum tersedia',
                'message' => 'Sertifikat diterbitkan setelah mentor mengumumkan nilai raport dan menandatangani penerbitan sertifikat.',
                'action_url' => route('participant.evaluation', [], false),
                'action_label' => 'Lihat Hasil Nilai Magang',
            ];
        }

        $ownEvaluation = $program->evaluations->contains(
            fn (Evaluation $evaluation) => (int) $evaluation->evaluator_id === $userId
        );

        if ((int) $program->id === (int) $this->currentProgram($request)?->id && ! $ownEvaluation) {
            return [
                'title' => 'Sertifikat menunggu kuesioner',
                'message' => 'Nilai sudah diumumkan. Isi kuesioner singkat di Hasil Nilai Magang untuk membuka sertifikat.',
                'action_url' => route('participant.evaluation', [], false),
                'action_label' => 'Isi kuesioner & lihat nilai',
            ];
        }

        if ($issued) {
            return [
                'title' => 'Sertifikat sedang diproses',
                'message' => 'Sertifikat sudah diterbitkan mentor. Muat ulang halaman ini jika baru saja menyelesaikan kuesioner.',
                'action_url' => null,
                'action_label' => null,
            ];
        }

        return [
            'title' => 'Menunggu penerbitan sertifikat',
            'message' => 'Nilai raport sudah ada. Mentor akan menerbitkan sertifikat setelah raport disahkan.',
            'action_url' => route('participant.evaluation', [], false),
            'action_label' => 'Lihat nilai raport',
        ];
    }

    private function mentorReportFor(Program $program, int $participantUserId): ?Evaluation
    {
        return $program->evaluations
            ->first(fn (Evaluation $evaluation) => (int) $evaluation->evaluator_id !== $participantUserId && $evaluation->hasReport());
    }

    private function participantCanViewCertificate(Program $program, int $participantUserId, ?int $currentProgramId): bool
    {
        if (! $this->mentorReportFor($program, $participantUserId)) {
            return false;
        }

        if ($currentProgramId !== null && (int) $program->id === (int) $currentProgramId) {
            return $program->evaluations->contains(
                fn (Evaluation $evaluation) => (int) $evaluation->evaluator_id === $participantUserId
            );
        }

        return true;
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        $request->user()->unreadNotifications->markAsRead();

        return view('participant.notifications', compact('notifications'));
    }

    public function settings()
    {
        return view('participant.settings');
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'password' => ['required', 'min:6', 'confirmed'],
        ]);
        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'Kata sandi diperbarui.');
    }

    /**
     * @return array<string, mixed>
     */
    private function registrationFieldRules(Request $request): array
    {
        return [
            'shared_goal' => ['required', 'string', 'min:20'],
            'activity_types' => ['required', 'array', 'min:1', 'max:2'],
            'activity_types.*' => ['nullable', 'string', Rule::in(Application::ACTIVITY_TYPES)],
            'problem_statement' => ['required', 'string', 'min:20'],
            'main_output' => ['required', 'string', 'min:10'],
            'participant_benefit' => ['required', 'string', 'min:20'],
            'business_benefit' => ['required', 'string', 'min:20'],
            'success_indicators' => [
                'required',
                'array',
                'min:'.Application::MIN_SUCCESS_INDICATORS,
                'max:'.Application::MAX_SUCCESS_INDICATORS,
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $filled = collect(is_array($value) ? $value : [])->map(fn ($item) => trim((string) $item))->filter();
                    if ($filled->count() < Application::MIN_SUCCESS_INDICATORS) {
                        $fail('Tulis minimal '.Application::MIN_SUCCESS_INDICATORS.' indikator keberhasilan.');

                        return;
                    }
                    foreach ($filled as $item) {
                        if (mb_strlen($item) < 10) {
                            $fail('Setiap indikator keberhasilan minimal 10 karakter.');
                            break;
                        }
                    }
                },
            ],
            'success_indicators.*' => ['nullable', 'string', 'max:255'],
            'indicator_feedback' => ['nullable', 'string'],
            'period_start' => ['required', 'date'],
            'period_end' => [
                'required',
                'date',
                'after:period_start',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $start = $request->date('period_start');
                    $end = $request->date('period_end');
                    if ($start && $end && ! Application::isTwoMonthPeriod($start, $end)) {
                        $fail('Periode harus tepat 2 bulan.');
                    }
                },
            ],
            'declaration' => ['accepted'],
        ];
    }

    private function authorizeApplication(Request $request, Application $application): void
    {
        abort_unless($application->participant_id === $request->user()->participant?->id, 403);
    }

    private function currentProgram(Request $request, bool $required = false): ?Program
    {
        $program = $this->programsQuery($request)->latest()->first();

        abort_if($required && ! $program, 404, 'Belum ada program aktif.');

        return $program;
    }

    /**
     * All internship cycles of the participant, newest first, for
     * cross-cycle history (outputs, final reports). Uploads still target
     * the current program only.
     *
     * @return Collection<int, Program>
     */
    private function allPrograms(Request $request)
    {
        return $this->programsQuery($request)->latest()->get();
    }

    private function programsQuery(Request $request)
    {
        return Program::with([
            'department', 'businessUnit', 'mentor.user', 'participant.user', 'agreement',
            'timelines', 'logbooks', 'mentorSessions', 'outputs', 'evaluations.evaluator', 'collaboration',
        ])->where('participant_id', $request->user()->participant?->id);
    }

    private function csv(string $value): array
    {
        return collect(explode(',', $value))->map(fn ($item) => trim($item))->filter()->values()->all();
    }

    private function evalRules(Request $request): array
    {
        // Nilai yang sah mengikuti skala Likert kelolaan admin.
        $scores = EvaluationScale::orderBy('position')->pluck('value')->all() ?: [1, 2, 3, 4, 5];

        return $request->validate([
            'criteria' => ['required', 'array', 'min:1', 'max:50'],
            'criteria.*' => ['required', 'array:label,score'],
            'criteria.*.label' => ['required', 'string', 'max:120'],
            'criteria.*.score' => ['required', 'integer', Rule::in($scores)],
            'comments' => ['nullable', 'string'],
        ]);
    }
}
