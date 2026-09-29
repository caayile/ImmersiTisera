<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BusinessUnit;
use App\Services\ApplicationApprovalService;
use App\Support\ApiPresenter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
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
            'primary_activity' => ['required', 'string', Rule::in(Application::ACTIVITY_TYPES)],
            'supporting_activity' => ['nullable', 'string', Rule::in(Application::ACTIVITY_TYPES)],
            'proposed_shared_goal' => ['required', 'string', 'min:20'],
            'problem_statement' => ['nullable', 'string', 'min:20'],
            'main_output' => ['nullable', 'string', 'min:10'],
            'participant_benefit' => ['nullable', 'string', 'min:20'],
            'business_benefit' => ['nullable', 'string', 'min:20'],
            'success_indicators' => ['nullable', 'array', 'max:'.Application::MAX_SUCCESS_INDICATORS],
            'success_indicators.*' => ['nullable', 'string', 'max:255'],
        ]);

        $participant = $request->user()->participant;
        abort_unless($participant?->study_program, 422, 'Lengkapi profil terlebih dahulu.');

        $unit = BusinessUnit::findOrFail($data['opportunity_id']);
        abort_unless($unit->status === 'open', 422, 'Lowongan departemen ini sudah ditutup.');
        $start = Carbon::parse(now()->toDateString());

        $activityTypes = collect([$data['primary_activity'], $data['supporting_activity'] ?? null])
            ->filter()
            ->unique()
            ->values()
            ->all();

        try {
            $application = $this->approvals->submit($participant, $unit, [
                'shared_goal' => $data['proposed_shared_goal'],
                'activity_types' => $activityTypes,
                'problem_statement' => $data['problem_statement'] ?? 'Fokus observasi dan pemetaan proses '.$unit->name.' selama program imersi.',
                'main_output' => $data['main_output'] ?? 'Hasil imersi sesuai kesepakatan mentor.',
                'participant_benefit' => $data['participant_benefit'] ?? 'Pengayaan materi dan jejaring industri bagi dosen.',
                'business_benefit' => $data['business_benefit'] ?? 'Sudut pandang akademik atas proses unit bisnis.',
                'success_indicators' => $data['success_indicators'] ?? [
                    'Luaran program selesai dan divalidasi mentor',
                    'Ada rencana tindak lanjut kolaborasi',
                ],
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
            'decision' => ['required', 'in:approved,revision'],
            'mentor_note' => ['required_if:decision,revision', 'nullable', 'string', 'min:10'],
            'success_indicators' => ['sometimes', 'array', 'max:'.Application::MAX_SUCCESS_INDICATORS],
            'success_indicators.*' => ['nullable', 'string', 'max:255'],
        ]);

        $mentor = $request->user()->mentor;
        abort_unless($mentor, 403);

        try {
            $application = $this->approvals->mentorReview($application, $mentor, [
                'decision' => $data['decision'],
                'mentor_note' => $data['mentor_note'] ?? null,
                ...isset($data['success_indicators']) ? ['success_indicators' => $data['success_indicators']] : [],
            ]);
        } catch (ValidationException $exception) {
            return response()->json(['message' => collect($exception->errors())->flatten()->first()], 422);
        }

        return response()->json($this->presenter->application($application));
    }
}
