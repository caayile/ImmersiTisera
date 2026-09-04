<?php

namespace App\Http\Controllers;

use App\Models\Checkpoint;
use App\Models\Evaluation;
use App\Models\Logbook;
use App\Models\Mentoring;
use App\Models\Program;
use App\Models\ProgramOutput;
use App\Models\Report;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Program::with([
            'dosen:id,name,email',
            'mentor:id,name,email',
            'opportunity',
            'agreement',
        ])->latest();

        if ($user->isUser()) {
            $query->where('dosen_id', $user->id);
        } elseif ($user->isMentor()) {
            $query->where('mentor_id', $user->id);
        }

        return response()->json($query->get()->map(fn (Program $program) => $this->present($program)));
    }

    public function show(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        $program->load([
            'dosen.dosenProfile',
            'mentor.mentorProfile',
            'opportunity',
            'agreement',
            'logbooks',
            'mentorings',
            'checkpoints',
            'evaluations.user:id,name,role',
            'report',
            'outputs',
        ]);

        if ($program->current_phase !== $program->computedPhase()) {
            $program->update(['current_phase' => $program->computedPhase()]);
        }

        return response()->json($this->present($program->fresh([
            'dosen.dosenProfile', 'mentor.mentorProfile', 'opportunity', 'agreement',
            'logbooks', 'mentorings', 'checkpoints', 'evaluations.user:id,name,role',
            'report', 'outputs',
        ]), true));
    }

    public function updatePhaseNotes(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        $data = $request->validate([
            'industry_insight' => ['nullable', 'string'],
            'problem_statement' => ['nullable', 'string'],
            'contribution_notes' => ['nullable', 'string'],
        ]);

        $program->update($data);

        return response()->json($this->present($program->fresh()));
    }

    public function storeLogbook(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program, 'user');

        $data = $request->validate([
            'entry_date' => ['required', 'date'],
            'what_did' => ['required', 'string'],
            'what_learned' => ['required', 'string'],
            'what_found' => ['required', 'string'],
            'obstacles' => ['nullable', 'string'],
            'output' => ['nullable', 'string'],
        ]);

        $entry = $program->logbooks()->create($data);

        return response()->json($entry, 201);
    }

    public function verifyLogbook(Request $request, Program $program, Logbook $logbook)
    {
        $this->authorizeAccess($request, $program, 'mentor');

        $logbook->update([
            'mentor_verified' => true,
            'verified_at' => now(),
        ]);

        return response()->json($logbook);
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

        $session = Mentoring::updateOrCreate(
            ['program_id' => $program->id, 'week' => $data['week']],
            $data
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

        $checkpoint = Checkpoint::updateOrCreate(
            ['program_id' => $program->id, 'week' => $data['week']],
            $data
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

        $type = $request->user()->isMentor() ? 'mentor' : 'dosen_self';

        $evaluation = Evaluation::updateOrCreate(
            ['program_id' => $program->id, 'user_id' => $request->user()->id],
            [...$data, 'type' => $type]
        );

        $avg = round($program->evaluations()->get()->avg(fn (Evaluation $item) => $item->average()), 1);
        $program->update(['collaboration_score' => $avg]);

        return response()->json([
            'evaluation' => $evaluation,
            'collaboration_score' => $avg,
        ]);
    }

    public function upsertReport(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        $data = $request->validate([
            'content' => ['required', 'array'],
            'status' => ['nullable', 'in:draft,submitted'],
        ]);

        $report = Report::updateOrCreate(
            ['program_id' => $program->id],
            [
                'content' => $data['content'],
                'status' => $data['status'] ?? 'draft',
            ]
        );

        return response()->json($report);
    }

    public function generateReport(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);
        $program->load(['agreement', 'opportunity', 'logbooks', 'mentorings', 'outputs', 'evaluations']);

        $content = [
            'objectives' => $program->agreement?->shared_goal,
            'industry_profile' => $program->opportunity?->title,
            'shared_goals' => $program->agreement?->shared_goal,
            'timeline' => $program->agreement?->timeline,
            'industry_insight' => $program->industry_insight,
            'problem_statement' => $program->problem_statement,
            'contribution' => $program->contribution_notes,
            'logbook_summary' => $program->logbooks->take(5)->map(fn ($item) => [
                'date' => $item->entry_date?->toDateString(),
                'found' => $item->what_found,
            ]),
            'mentoring' => $program->mentorings->map(fn ($item) => [
                'week' => $item->week,
                'next_action' => $item->next_action,
                'feedback' => $item->mentor_feedback,
            ]),
            'evaluation' => $program->collaboration_score,
            'output' => $program->outputs->pluck('title'),
            'recommendation' => $program->connect_notes,
            'potential_collaboration' => $program->agreement?->potential_collaboration,
        ];

        $report = Report::updateOrCreate(
            ['program_id' => $program->id],
            ['content' => $content, 'status' => 'draft']
        );

        return response()->json($report);
    }

    public function storeOutput(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        $data = $request->validate([
            'category' => ['required', 'in:research,learning,industry'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        $output = $program->outputs()->create($data);

        return response()->json($output, 201);
    }

    public function connect(Request $request, Program $program)
    {
        $this->authorizeAccess($request, $program);

        $data = $request->validate([
            'connect_decision' => ['required', 'in:close,follow_up,collaborate,develop,scale'],
            'connect_notes' => ['nullable', 'string'],
        ]);

        $maturity = match ($data['connect_decision']) {
            'close' => 1,
            'follow_up' => 1,
            'collaborate' => 2,
            'develop' => 3,
            'scale' => 4,
        };

        $program->update([
            ...$data,
            'maturity_level' => $maturity,
            'status' => $data['connect_decision'] === 'close' ? 'closed' : 'completed',
            'current_phase' => 'connect',
        ]);

        return response()->json($this->present($program->fresh()));
    }

    private function authorizeAccess(Request $request, Program $program, ?string $role = null): void
    {
        $user = $request->user();
        $allowed = $user->isAdmin() || $program->dosen_id === $user->id || $program->mentor_id === $user->id;
        abort_unless($allowed, 403);

        if ($role === 'user') {
            abort_unless($user->isUser() || $user->isAdmin(), 403);
        }

        if ($role === 'mentor') {
            abort_unless($user->isMentor() || $user->isAdmin(), 403);
        }
    }

    private function present(Program $program, bool $full = false): array
    {
        $payload = [
            ...$program->toArray(),
            'computed_phase' => $program->computedPhase(),
            'current_week' => $program->currentWeek(),
        ];

        if (! $full) {
            unset($payload['logbooks'], $payload['mentorings'], $payload['checkpoints'], $payload['evaluations'], $payload['outputs']);
        }

        return $payload;
    }
}
