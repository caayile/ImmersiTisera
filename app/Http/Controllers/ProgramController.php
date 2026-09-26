<?php

namespace App\Http\Controllers;

use App\Models\CollaborationPipeline;
use App\Models\Evaluation;
use App\Models\Logbook;
use App\Models\MentorSession;
use App\Models\Program;
use App\Models\ProgramOutput;
use App\Models\Timeline;
use App\Support\ApiPresenter;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function __construct(private ApiPresenter $presenter) {}

    public function index(Request $request)
    {
        $query = Program::query()
            ->with(['participant.user', 'mentor.user', 'businessUnit', 'agreement', 'collaboration'])
            ->latest('id');

        $this->scopePrograms($request, $query);

        return response()->json($query->get()->map(fn (Program $program) => $this->presenter->program($program)));
    }

    public function show(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        return response()->json($this->presenter->program($program, true));
    }

    public function history(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isParticipant() || $user->isAdmin(), 403);

        $query = Program::query()
            ->with(['businessUnit.department', 'department', 'mentor.user', 'logbooks'])
            ->latest('id');

        if ($user->isParticipant()) {
            $query->where('participant_id', $user->participant?->id);
        }

        return response()->json($query->get()->map(fn (Program $program) => [
            'id' => $program->id,
            'status' => $program->status,
            'current_week' => $program->current_week,
            'start_date' => $program->start_date?->toDateString(),
            'end_date' => $program->end_date?->toDateString(),
            'department' => $program->department?->name ?? $program->businessUnit?->department?->name,
            'opportunity' => $program->businessUnit?->name,
            'mentor' => $program->mentor?->user?->name,
            'logbooks' => $program->logbooks
                ->sortByDesc('date')
                ->map(fn (Logbook $item) => $this->presenter->logbook($item))
                ->values(),
        ]));
    }

    public function updatePhaseNotes(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        $data = $request->validate([
            'industry_insight' => ['nullable', 'string'],
            'problem_statement' => ['nullable', 'string'],
            'contribution_notes' => ['nullable', 'string'],
        ]);

        $program->agreement()->updateOrCreate(
            ['program_id' => $program->id],
            [
                'problem_statement' => $data['problem_statement'] ?? $data['industry_insight'] ?? null,
                'activities' => $data['contribution_notes'] ?? null,
            ]
        );

        return response()->json($this->presenter->program($program->fresh(['agreement', 'participant.user', 'mentor.user', 'businessUnit'])));
    }

    public function storeLogbook(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program, 'participant');

        $data = $request->validate([
            'entry_date' => ['required', 'date'],
            'what_did' => ['required', 'string'],
            'what_learned' => ['required', 'string'],
            'what_found' => ['required', 'string'],
            'obstacles' => ['nullable', 'string'],
            'output' => ['nullable', 'string'],
        ]);

        $entry = $program->logbooks()->create([
            'participant_id' => $program->participant_id,
            'date' => $data['entry_date'],
            'activity' => 'Logbook harian',
            'what_i_did' => $data['what_did'],
            'what_i_learned' => $data['what_learned'],
            'what_i_found' => $data['what_found'],
            'value' => $data['obstacles'] ?? null,
            'next_action' => $data['output'] ?? null,
            'status' => 'submitted',
        ]);

        return response()->json($this->presenter->logbook($entry), 201);
    }

    public function verifyLogbook(Request $request, Program $program, Logbook $logbook)
    {
        $this->authorizeAccess($request, $program, 'mentor');
        abort_unless($logbook->program_id === $program->id, 404);

        $logbook->update(['status' => 'approved']);

        return response()->json($this->presenter->logbook($logbook->fresh()));
    }

    public function storeMentoring(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        $data = $request->validate([
            'week' => ['required', 'integer', 'min:1', 'max:8'],
            'session_date' => ['nullable', 'date'],
            'found' => ['nullable', 'string'],
            'working_on' => ['nullable', 'string'],
            'next_action' => ['nullable', 'string'],
            'mentor_feedback' => ['nullable', 'string'],
        ]);

        $session = MentorSession::updateOrCreate(
            ['program_id' => $program->id, 'week' => $data['week']],
            [
                'mentor_id' => $program->mentor_id,
                'participant_id' => $program->participant_id,
                'session_date' => $data['session_date'] ?? now()->toDateString(),
                'findings' => $data['found'] ?? null,
                'current_work' => $data['working_on'] ?? null,
                'next_action' => $data['next_action'] ?? null,
                'feedback' => $data['mentor_feedback'] ?? null,
            ]
        );

        return response()->json($session);
    }

    public function storeCheckpoint(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        $data = $request->validate([
            'week' => ['required', 'integer', 'min:1', 'max:8'],
            'dosen_progress' => ['nullable', 'string'],
            'mentor_status' => ['nullable', 'in:agree,need_improvement'],
            'mentor_notes' => ['nullable', 'string'],
        ]);

        $checkpoint = Timeline::updateOrCreate(
            ['program_id' => $program->id, 'week' => $data['week']],
            [
                'phase' => $this->presenter->phase($data['week']),
                'title' => 'Minggu '.$data['week'],
                'description' => $data['dosen_progress'] ?? null,
                'expected_output' => $data['mentor_notes'] ?? null,
                'status' => ($data['mentor_status'] ?? null) === 'agree' ? 'done' : 'pending',
            ]
        );

        return response()->json($checkpoint);
    }

    public function storeEvaluation(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        $data = $request->validate([
            'industry_understanding' => ['required', 'integer', 'min:1', 'max:5'],
            'relationship' => ['required', 'integer', 'min:1', 'max:5'],
            'output_quality' => ['required', 'integer', 'min:1', 'max:5'],
            'mutual_benefit' => ['required', 'integer', 'min:1', 'max:5'],
            'collaboration_potential' => ['required', 'integer', 'min:1', 'max:5'],
            'comments' => ['nullable', 'string'],
        ]);

        $evaluation = Evaluation::updateOrCreate(
            ['program_id' => $program->id, 'evaluator_id' => $request->user()->id],
            [
                'industry_understanding' => $data['industry_understanding'],
                'relationship' => $data['relationship'],
                'output' => $data['output_quality'],
                'mutual_benefit' => $data['mutual_benefit'],
                'collaboration_potential' => $data['collaboration_potential'],
                'comments' => $data['comments'] ?? null,
            ]
        );

        return response()->json([
            'evaluation' => $evaluation,
            'collaboration_score' => round($program->evaluations()->get()->avg(fn (Evaluation $item) => $item->average()), 1),
        ]);
    }

    public function generateReport(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        $program->load(['agreement', 'businessUnit', 'logbooks', 'mentorSessions', 'outputs', 'evaluations']);

        $content = [
            'objectives' => $program->agreement?->objective,
            'industry_profile' => $program->businessUnit?->name,
            'shared_goals' => $program->agreement?->objective,
            'problem_statement' => $program->agreement?->problem_statement,
            'logbook_summary' => $program->logbooks->take(5)->map(fn (Logbook $item) => [
                'date' => $item->date?->toDateString(),
                'found' => $item->what_i_found,
            ]),
        ];

        $report = ProgramOutput::updateOrCreate(
            ['program_id' => $program->id, 'is_final_report' => true],
            [
                'participant_id' => $program->participant_id,
                'title' => 'Laporan akhir magang dosen',
                'type' => 'Research Report',
                'description' => json_encode($content),
                'status' => 'draft',
                'is_final_report' => true,
            ]
        );

        return response()->json(['id' => $report->id, 'content' => $content, 'status' => $report->status]);
    }

    public function storeOutput(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        $data = $request->validate([
            'category' => ['required', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        $output = $program->outputs()->create([
            'participant_id' => $program->participant_id,
            'title' => $data['title'],
            'type' => $data['category'],
            'description' => $data['description'] ?? null,
            'status' => 'submitted',
        ]);

        return response()->json($output, 201);
    }

    public function connect(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        $data = $request->validate([
            'connect_decision' => ['required', 'in:close,follow_up,collaborate,develop,scale'],
            'connect_notes' => ['nullable', 'string'],
        ]);

        $level = match ($data['connect_decision']) {
            'close' => 0,
            'follow_up' => 1,
            'collaborate' => 2,
            'develop' => 3,
            'scale' => 4,
        };

        CollaborationPipeline::updateOrCreate(
            ['program_id' => $program->id],
            [
                'level' => $level,
                'collaboration_type' => $data['connect_decision'],
                'notes' => $data['connect_notes'] ?? null,
            ]
        );

        $program->update(['status' => $data['connect_decision'] === 'close' ? 'completed' : $program->status]);

        return response()->json($this->presenter->program($program->fresh(['collaboration', 'participant.user', 'mentor.user', 'businessUnit'])));
    }

    private function scopePrograms(Request $request, $query): void
    {
        $user = $request->user();

        if ($user->isParticipant()) {
            $query->where('participant_id', $user->participant?->id);
        } elseif ($user->isMentor()) {
            $query->where('mentor_id', $user->mentor?->id);
        }
    }

    private function authorizeAccess(Request $request, Program $program, ?string $role = null): void
    {
        $user = $request->user();
        $allowed = $user->isAdmin()
            || $program->participant?->user_id === $user->id
            || $program->mentor?->user_id === $user->id;

        abort_unless($allowed, 403);

        if ($role === 'participant') {
            abort_unless($user->isParticipant() || $user->isAdmin(), 403);
        }

        if ($role === 'mentor') {
            abort_unless($user->isMentor() || $user->isAdmin(), 403);
        }
    }
}
