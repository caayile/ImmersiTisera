<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\CollaborationPipeline;
use App\Models\Evaluation;
use App\Models\Logbook;
use App\Models\MentorSession;
use App\Models\Program;
use App\Models\ProgramOutput;
use App\Notifications\ImersiAlert;
use App\Support\Status;
use Illuminate\Http\Request;

class MentorController extends Controller
{
    public function dashboard(Request $request)
    {
        $mentor = $request->user()->mentor;
        $programs = Program::with(['participant.user', 'businessUnit'])->where('mentor_id', $mentor?->id)->get();

        return view('mentor.dashboard', [
            'mentor' => $mentor,
            'programs' => $programs,
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

    public function agreements(Request $request)
    {
        $agreements = Agreement::with(['program.participant.user', 'program.businessUnit'])
            ->whereHas('program', fn ($q) => $q->where('mentor_id', $request->user()->mentor?->id))
            ->latest()
            ->get();

        return view('mentor.agreements', compact('agreements'));
    }

    public function reviewAgreement(Request $request, Agreement $agreement)
    {
        $this->authorizeProgram($request, $agreement->program);
        $data = $request->validate([
            'decision' => ['required', 'in:agreed,revision'],
            'revision_note' => ['nullable', 'string'],
        ]);

        if ($data['decision'] === 'revision') {
            $agreement->update(['status' => 'revision', 'revision_note' => $data['revision_note'], 'mentor_approved_at' => null]);
            $agreement->program->update(['status' => 'revision']);
            $agreement->program->participant->user->notify(new ImersiAlert('Agreement perlu revisi', $data['revision_note'] ?? 'Silakan perbaiki agreement.', route('participant.agreement')));
        } else {
            $agreement->update(['status' => 'agreed', 'mentor_approved_at' => now()]);
            $program = $agreement->program;
            $program->update([
                'status' => 'active',
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDays(60)->toDateString(),
            ]);
            $program->seedTimeline();
            $program->participant->user->notify(new ImersiAlert('Program ACTIVE', 'Agreement disetujui. Immersion dimulai.', route('participant.program')));
        }

        return back()->with('status', 'Keputusan agreement disimpan.');
    }

    public function timeline(Request $request)
    {
        return view('mentor.timeline', ['programs' => $this->mine($request)->with('timelines')->get()]);
    }

    public function logbooks(Request $request)
    {
        $logbooks = Logbook::with(['program.participant.user'])
            ->whereHas('program', fn ($q) => $q->where('mentor_id', $request->user()->mentor?->id))
            ->latest()
            ->get();

        return view('mentor.logbooks', compact('logbooks'));
    }

    public function reviewLogbook(Request $request, Logbook $logbook)
    {
        $this->authorizeProgram($request, $logbook->program);
        $data = $request->validate([
            'status' => ['required', 'in:reviewed,revision,approved'],
            'mentor_feedback' => ['nullable', 'string'],
        ]);
        $logbook->update($data);
        $logbook->program->participant->user->notify(new ImersiAlert('Update logbook', 'Status logbook: '.$data['status'], route('participant.logbooks')));

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
        $output->program->participant->user->notify(new ImersiAlert('Output '.$data['status'], $output->title, route('participant.outputs')));

        return back()->with('status', 'Output divalidasi.');
    }

    public function evaluations(Request $request)
    {
        return view('mentor.evaluations', ['programs' => $this->mine($request)->with(['participant.user', 'evaluations'])->get()]);
    }

    public function storeEvaluation(Request $request, Program $program)
    {
        $this->authorizeProgram($request, $program);
        $data = $request->validate([
            'industry_understanding' => ['required', 'integer', 'min:1', 'max:5'],
            'relationship' => ['required', 'integer', 'min:1', 'max:5'],
            'output' => ['required', 'integer', 'min:1', 'max:5'],
            'mutual_benefit' => ['required', 'integer', 'min:1', 'max:5'],
            'collaboration_potential' => ['required', 'integer', 'min:1', 'max:5'],
            'comments' => ['nullable', 'string'],
        ]);
        Evaluation::updateOrCreate(['program_id' => $program->id, 'evaluator_id' => $request->user()->id], $data);

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

        return back()->with('status', 'Pipeline kolaborasi disimpan.');
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
