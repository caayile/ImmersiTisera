<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BusinessUnit;
use App\Services\ApplicationApprovalService;
use App\Support\ApiPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ApplicationController extends Controller
{
    public function __construct(private ApiPresenter $presenter, private ApplicationApprovalService $approvals) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Application::query()
            ->with(['participant.user', 'businessUnit.department', 'mentor.user', 'program.agreement'])
            ->latest('id');

        if ($user->isParticipant()) {
            $query->where('participant_id', $user->participant?->id);
        } elseif ($user->isMentor()) {
            $query->where('mentor_id', $user->mentor?->id);
        }

        return response()->json($query->get()->map(fn (Application $application) => $this->presenter->application($application)));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isParticipant(), 403);

        $data = $request->validate([
            'opportunity_id' => ['required', 'exists:business_units,id'],
            'primary_activity' => ['required', 'string'],
            'supporting_activity' => ['nullable', 'string'],
            'proposed_shared_goal' => ['required', 'string'],
        ]);

        $participant = $request->user()->participant;
        abort_unless($participant?->study_program, 422, 'Lengkapi profil terlebih dahulu.');

        $unit = BusinessUnit::findOrFail($data['opportunity_id']);
        $start = Carbon::parse(now()->toDateString());

        try {
            $application = $this->approvals->submit($participant, $unit, [
                'motivation' => $data['proposed_shared_goal'],
                'learning_objectives' => ($data['supporting_activity'] ?? null) ?: $data['primary_activity'],
                'planned_activities' => $data['primary_activity'],
                'expected_output' => 'Hasil imersi sesuai kesepakatan mentor.',
                'campus_benefit' => 'Pengayaan materi dan jejaring industri.',
                'period_start' => $start->toDateString(),
                'period_end' => Application::periodEndFromStart($start)->toDateString(),
                'cv_path' => null,
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['message' => collect($exception->errors())->flatten()->first()], 422);
        }

        return response()->json($this->presenter->application($application), 201);
    }

    public function review(Request $request, Application $application)
    {
        abort_unless($request->user()->isMentor(), 403);

        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected,revision'],
            'mentor_note' => ['nullable', 'string'],
        ]);

        $mentor = $request->user()->mentor;
        abort_unless($mentor, 403);

        try {
            $application = $this->approvals->mentorReview($application, $mentor, [
                'decision' => $data['decision'] === 'approved' ? 'approved' : $data['decision'],
                'mentor_note' => $data['mentor_note'] ?? null,
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['message' => collect($exception->errors())->flatten()->first()], 422);
        }

        return response()->json($this->presenter->application($application));
    }
}
