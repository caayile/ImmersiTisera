<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Application;
use App\Models\CollaborationPipeline;
use App\Models\Evaluation;
use App\Models\Logbook;
use App\Models\MentorSession;
use App\Models\Program;
use App\Models\ProgramOutput;
use App\Models\Timeline;
use App\Notifications\ImersiAlert;
use App\Services\AgreementLetterService;
use App\Services\ApplicationApprovalService;
use App\Support\Status;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MentorController extends Controller
{
    public function dashboard(Request $request)
    {
        $mentor = $request->user()->mentor;
        $programs = Program::with(['participant.user', 'businessUnit'])->where('mentor_id', $mentor?->id)->get();

        return view('mentor.dashboard', [
            'mentor' => $mentor,
            'programs' => $programs,
            'pendingApplications' => Application::where('mentor_id', $mentor?->id)->where('status', 'waiting_mentor')->count(),
            'pendingAgreements' => Agreement::whereHas('program', fn ($q) => $q->where('mentor_id', $mentor?->id))->whereIn('status', ['submitted', 'revision'])->count(),
            'pendingLogbooks' => Logbook::whereHas('program', fn ($q) => $q->where('mentor_id', $mentor?->id))->where('status', 'submitted')->count(),
            'pendingOutputs' => ProgramOutput::whereHas('program', fn ($q) => $q->where('mentor_id', $mentor?->id))->where('status', 'submitted')->count(),
        ]);
    }

    public function participants(Request $request)
    {
        $programs = $this->mine($request)->with(['participant.user', 'businessUnit', 'logbooks', 'outputs'])->get();

        return view('mentor.participants', compact('programs'));
    }

    public function showParticipant(Request $request, Program $program)
    {
        $this->authorizeProgram($request, $program);

        return view('mentor.participant-show', ['program' => $program->load(['participant.user', 'agreement', 'logbooks', 'outputs', 'mentorSessions', 'timelines'])]);
    }

    public function programs(Request $request)
    {
        return view('mentor.programs', ['programs' => $this->mine($request)->with(['participant.user', 'businessUnit', 'agreement'])->get()]);
    }

    public function applications(Request $request)
    {
        $applications = Application::with(['participant.user', 'department', 'businessUnit', 'mentor.user'])
            ->where('mentor_id', $request->user()->mentor?->id)
            ->latest()
            ->get();

        return view('mentor.applications', compact('applications'));
    }

    public function showApplication(Request $request, Application $application)
    {
        $mentor = $request->user()->mentor;
        abort_unless($mentor && $application->mentor_id === $mentor->id, 403);

        $application->load(['participant.user', 'department', 'businessUnit', 'mentor.user']);

        return view('mentor.application-show', compact('application'));
    }

    public function reviewApplication(Request $request, Application $application, ApplicationApprovalService $approvals)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approved,revision'],
            'mentor_note' => ['required_if:decision,revision', 'nullable', 'string', 'min:10', 'max:2000'],
            'revision_note' => ['nullable', 'string', 'max:2000'],
            'success_indicators' => ['sometimes', 'array', 'max:'.Application::MAX_SUCCESS_INDICATORS],
            'success_indicators.*' => ['nullable', 'string', 'max:255'],
        ]);

        $mentor = $request->user()->mentor;
        abort_unless($mentor, 403);

        if ($application->mentor_id === $mentor->id && $data['decision'] === 'approved' && $application->status === 'waiting_admin') {
            return redirect()
                ->route('mentor.applications.show', $application)
                ->with('status', 'Persetujuan ini sudah disetujui (status: '.$application->currentStageLabel().'). Muat ulang halaman untuk melihat status terbaru.');
        }

        $approvals->mentorReview($application, $mentor, $data);

        $message = $data['decision'] === 'revision'
            ? 'Permintaan revisi dikirim ke dosen.'
            : 'Surat persetujuan ditandatangani dan diteruskan ke admin.';

        return redirect()
            ->route('mentor.applications')
            ->with('status', $message);
    }

    public function agreements(Request $request)
    {
        $agreements = Agreement::with(['program.participant.user', 'program.businessUnit'])
            ->whereHas('program', fn ($q) => $q->where('mentor_id', $request->user()->mentor?->id))
            ->latest()
            ->get();

        return view('mentor.agreements', compact('agreements'));
    }

    public function printAgreement(Request $request, Agreement $agreement)
    {
        $this->authorizeProgram($request, $agreement->program);
        abort_unless($agreement->status === 'agreed', 404);

        if (blank($agreement->letter_number)) {
            app(AgreementLetterService::class)->issue($agreement);
            $agreement->refresh();
        }

        return view('participant.agreement-print', [
            'program' => $agreement->program->load(['participant.user', 'mentor.user', 'department', 'businessUnit', 'agreement', 'application']),
            'agreement' => $agreement,
            'pdf' => false,
        ]);
    }

    public function downloadAgreementPdf(Request $request, Agreement $agreement)
    {
        $this->authorizeProgram($request, $agreement->program);
        abort_unless($agreement->status === 'agreed', 404);

        if (blank($agreement->letter_number)) {
            app(AgreementLetterService::class)->issue($agreement);
            $agreement->refresh();
        }

        $program = $agreement->program->load(['participant.user', 'mentor.user', 'department', 'businessUnit', 'agreement', 'application']);

        $filename = 'Perjanjian-Magang-'.preg_replace('/[^A-Za-z0-9]+/', '-', (string) ($agreement->letter_number ?? 'tanpa-nomor')).'.pdf';

        return Pdf::loadView('participant.agreement-print', [
            'program' => $program,
            'agreement' => $agreement,
            'pdf' => true,
        ])->setPaper('a4', 'portrait')->download($filename);
    }

    public function reviewAgreement(Request $request, Agreement $agreement)
    {
        $this->authorizeProgram($request, $agreement->program);
        $data = $request->validate([
            'decision' => ['required', 'in:agreed,revision'],
            'revision_note' => ['nullable', 'string'],
            'mentor_signature' => [
                'required_if:decision,agreed',
                'nullable',
                'string',
                'regex:/^data:image\/(png|jpeg|jpg|webp);base64,/i',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_string($value) && strlen($value) > 900_000) {
                        $fail('Tanda tangan mentor terlalu besar.');
                    }
                },
            ],
        ]);

        if ($data['decision'] === 'revision') {
            $agreement->update(['status' => 'revision', 'revision_note' => $data['revision_note'], 'mentor_approved_at' => null]);
            $agreement->program->update(['status' => 'revision']);
            $agreement->program->participant->user->notify(new ImersiAlert('Perjanjian perlu revisi', $data['revision_note'] ?? 'Silakan perbaiki perjanjian.', route('participant.agreement')));
        } else {
            $agreement->update([
                'status' => 'agreed',
                'mentor_approved_at' => now(),
                'mentor_signature' => $data['mentor_signature'],
            ]);
            app(AgreementLetterService::class)->issue($agreement->fresh());
            $program = $agreement->program;
            $program->update([
                'status' => 'active',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(60)->toDateString(),
            ]);
            $program->seedTimeline();
            $program->participant->user->notify(new ImersiAlert('Program aktif', 'Perjanjian disetujui. Masa magang dimulai.', route('participant.program')));
        }

        return back()->with('status', 'Keputusan perjanjian disimpan.');
    }

    public function timeline(Request $request)
    {
        return view('mentor.timeline', ['programs' => $this->mine($request)->with(['participant.user', 'businessUnit', 'department', 'timelines'])->get()]);
    }

    public function showTimeline(Request $request, Program $program)
    {
        $this->authorizeProgram($request, $program);

        return view('mentor.timeline-show', ['program' => $program->load(['participant.user', 'businessUnit', 'department', 'timelines'])]);
    }

    public function reviewTimeline(Request $request, Timeline $timeline)
    {
        $this->authorizeProgram($request, $timeline->program);

        $data = $request->validate([
            'status' => ['required', 'in:done,pending'],
            'mentor_note' => ['required_if:status,pending', 'nullable', 'string'],
        ]);

        $timeline->update([
            'status' => $data['status'],
            'mentor_note' => $data['status'] === 'pending' ? $data['mentor_note'] : null,
        ]);
        $timeline->program->participant->user->notify(new ImersiAlert(
            'Checkpoint minggu '.$timeline->week.' '.$data['status'],
            $data['status'] === 'done' ? 'Mentor mengesahkan checkpoint.' : ($data['mentor_note'] ?? 'Mentor meminta perbaikan.'),
            route('participant.timeline')
        ));

        return back()->with('status', 'Checkpoint diperbarui.');
    }

    public function logbooks(Request $request)
    {
        $query = $this->mine($request)->with(['participant.user', 'department', 'businessUnit', 'logbooks']);

        if ($request->filled('q')) {
            $needle = mb_strtolower($request->string('q')->toString(), 'UTF-8');
            $query->whereHas('participant.user', fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%'.$needle.'%']));
        }

        foreach (['department_id', 'business_unit_id'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('status')) {
            $query->whereHas('logbooks', fn ($q) => $q->where('status', $request->input('status')));
        }

        $programs = $query->get();

        return view('mentor.logbooks', [
            'programs' => $programs,
            'departments' => $programs->pluck('department')->filter()->unique('id')->sortBy('name'),
            'businessUnits' => $programs->pluck('businessUnit')->filter()->unique('id')->sortBy('name'),
        ]);
    }

    public function showLogbooks(Request $request, Program $program)
    {
        $this->authorizeProgram($request, $program);

        $program->load(['participant.user', 'department', 'businessUnit', 'logbooks']);

        return view('logbooks.show', [
            'program' => $program,
            'month' => $this->showMonth($request, $program),
            'byDate' => $program->logbooks->sortBy(['date', 'id'])->groupBy(fn (Logbook $log) => $log->date->toDateString()),
            'backRoute' => 'mentor.logbooks',
            'canReview' => true,
        ]);
    }

    private function showMonth(Request $request, Program $program): Carbon
    {
        try {
            $parsed = trim((string) $request->string('month'));

            if ($parsed !== '') {
                return Carbon::createFromFormat('Y-m', $parsed)->startOfMonth();
            }
        } catch (\Exception) {
            // Fall through to the program-based default below.
        }

        return $program->start_date?->copy()->startOfMonth() ?? today()->startOfMonth();
    }

    public function reviewLogbook(Request $request, Logbook $logbook)
    {
        $this->authorizeProgram($request, $logbook->program);
        $data = $request->validate([
            'status' => ['required', 'in:reviewed,revision,approved'],
            'mentor_feedback' => ['nullable', 'string'],
        ]);
        $logbook->update($data);
        $logbook->program->participant->user->notify(new ImersiAlert('Logbook diperbarui', 'Status logbook: '.Status::logbookLabel($data['status']), route('participant.logbooks')));

        return back()->with('status', 'Logbook diperbarui.');
    }

    public function mentoring(Request $request)
    {
        $sessions = MentorSession::with(['program.participant.user'])
            ->where('mentor_id', $request->user()->mentor?->id)
            ->latest()
            ->get();

        return view('mentor.mentoring', [
            'sessions' => $sessions,
            'programs' => $this->mine($request)->where('status', 'active')->with('participant.user')->get(),
        ]);
    }

    public function storeMentoring(Request $request)
    {
        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'week' => ['required', 'integer', 'min:1', 'max:8'],
            'session_date' => ['nullable', 'date'],
            'findings' => ['nullable', 'string'],
            'current_work' => ['nullable', 'string'],
            'next_action' => ['nullable', 'string'],
            'feedback' => ['nullable', 'string'],
            'checkpoint_status' => ['nullable', 'in:on_track,need_improvement'],
        ]);
        $program = Program::findOrFail($data['program_id']);
        $this->authorizeProgram($request, $program);

        MentorSession::updateOrCreate(
            ['program_id' => $program->id, 'week' => $data['week']],
            [...$data, 'mentor_id' => $program->mentor_id, 'participant_id' => $program->participant_id]
        );
        $program->participant->user->notify(new ImersiAlert('Mentoring minggu '.$data['week'], 'Sesi mentoring diperbarui.', route('participant.mentoring')));

        return back()->with('status', 'Sesi mentoring disimpan.');
    }

    public function outputs(Request $request)
    {
        $outputs = ProgramOutput::with(['program.participant.user'])
            ->whereHas('program', fn ($q) => $q->where('mentor_id', $request->user()->mentor?->id))
            ->latest()
            ->get();

        return view('mentor.outputs', compact('outputs'));
    }

    public function reviewOutput(Request $request, ProgramOutput $output)
    {
        $this->authorizeProgram($request, $output->program);
        $data = $request->validate([
            'status' => ['required', 'in:approved,revision'],
            'mentor_feedback' => ['nullable', 'string'],
        ]);
        $output->update($data);
        $output->program->participant->user->notify(new ImersiAlert('Hasil: '.Status::outputLabel($data['status']), $output->title, route('participant.outputs')));

        return back()->with('status', 'Hasil berhasil divalidasi.');
    }

    public function evaluations(Request $request)
    {
        $programs = $this->mine($request)->with(['participant.user', 'businessUnit', 'evaluations.evaluator', 'evaluations.program.participant.user', 'evaluations.program.businessUnit'])->get();
        $evaluatorId = $request->user()->id;

        $given = $programs->flatMap(fn (Program $program) => $program->evaluations)
            ->where('evaluator_id', $evaluatorId)
            ->values();
        $received = $programs->flatMap(fn (Program $program) => $program->evaluations)
            ->whereNotIn('evaluator_id', [$evaluatorId])
            ->values();

        return view('mentor.evaluations', compact('programs', 'given', 'received'));
    }

    public function storeEvaluation(Request $request, Program $program)
    {
        $this->authorizeProgram($request, $program);

        if ($request->has('groups')) {
            $data = $request->validate([
                'groups' => ['required', 'array', 'min:1', 'max:10'],
                'groups.*.name' => ['required', 'string', 'max:120'],
                'groups.*.weight' => ['required', 'numeric', 'min:0', 'max:100'],
                'groups.*.aspects' => ['required', 'array', 'min:1', 'max:20'],
                'groups.*.aspects.*.label' => ['required', 'string', 'max:120'],
                'groups.*.aspects.*.score' => ['required', 'integer', 'min:0', 'max:100'],
                'comments' => ['nullable', 'string'],
            ]);

            Evaluation::updateOrCreate(
                ['program_id' => $program->id, 'evaluator_id' => $request->user()->id],
                [
                    'grade_groups' => array_map(fn (array $group) => [
                        'name' => trim($group['name']),
                        'weight' => (float) $group['weight'],
                        'aspects' => array_map(fn (array $aspect) => [
                            'label' => trim($aspect['label']),
                            'score' => (int) $aspect['score'],
                        ], $group['aspects']),
                    ], $data['groups']),
                    'criteria' => null,
                    'industry_understanding' => null,
                    'relationship' => null,
                    'output' => null,
                    'mutual_benefit' => null,
                    'collaboration_potential' => null,
                    'comments' => $data['comments'] ?? null,
                ]
            );

            return back()->with('status', 'Nilai raport tersimpan.');
        }

        $data = $request->validate([
            'criteria' => ['required', 'array', 'min:1', 'max:15'],
            'criteria.*' => ['required', 'array:label,score'],
            'criteria.*.label' => ['required', 'string', 'max:120'],
            'criteria.*.score' => ['required', 'integer', 'min:1', 'max:5'],
            'comments' => ['nullable', 'string'],
        ]);
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

        return back()->with('status', 'Evaluasi tersimpan.');
    }

    public function collaborations(Request $request)
    {
        return view('mentor.collaborations', [
            'programs' => $this->mine($request)->with(['participant.user', 'collaboration'])->get(),
            'levels' => Status::COLLABORATION_LEVELS,
            'types' => Status::COLLABORATION_TYPES,
        ]);
    }

    public function updateCollaboration(Request $request, Program $program)
    {
        $this->authorizeProgram($request, $program);
        $data = $request->validate([
            'level' => ['required', 'integer', 'min:0', 'max:4'],
            'collaboration_type' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'next_action' => ['nullable', 'string'],
            'responsible_person' => ['nullable', 'string'],
            'target_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);
        CollaborationPipeline::updateOrCreate(['program_id' => $program->id], $data);
        $program->participant->user->notify(new ImersiAlert('Kolaborasi diperbarui', Status::COLLABORATION_LEVELS[$data['level']], route('participant.collaboration')));

        return back()->with('status', 'Rencana kolaborasi disimpan.');
    }

    public function notifications(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        $request->user()->unreadNotifications->markAsRead();

        return view('mentor.notifications', compact('notifications'));
    }

    private function mine(Request $request)
    {
        return Program::where('mentor_id', $request->user()->mentor?->id)->latest();
    }

    private function authorizeProgram(Request $request, Program $program): void
    {
        abort_unless($program->mentor_id === $request->user()->mentor?->id, 403);
    }
}
