<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\BusinessUnit;
use App\Services\MatchingService;
use App\Support\ApiPresenter;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function __construct(private ApiPresenter $presenter, private MatchingService $matching) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = BusinessUnit::query()
            ->with(['department', 'mentors.user'])
            ->orderByDesc('id');

        if ($user->isMentor()) {
            $mentor = $user->mentor;
            $query->where(function ($inner) use ($mentor) {
                $inner->where('id', $mentor?->business_unit_id)
                    ->orWhere('department_id', $mentor?->department_id);
            });
        }

        $units = $query->get()->unique('id')->values();
        $participant = $user->participant;
        $appliedIds = $participant
            ? Application::query()->where('participant_id', $participant->id)->pluck('business_unit_id')->all()
            : [];

        return response()->json($units->map(function (BusinessUnit $unit) use ($participant, $appliedIds) {
            $match = $participant ? $this->matching->score($participant, $unit) : ['score' => 0];

            return $this->presenter->opportunity($unit, $match, in_array($unit->id, $appliedIds, true));
        }));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->isMentor(), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'problem' => ['required', 'string'],
            'opportunity' => ['required', 'string'],
            'needed_expertise' => ['required', 'array', 'min:1'],
            'expected_output' => ['required', 'string'],
            'purpose' => ['nullable', 'string', 'max:120'],
        ]);

        $mentor = $request->user()->mentor;
        abort_unless($mentor?->department_id, 422, 'Lengkapi profil mentor dan pilih unit bisnis.');

        $unit = $mentor->businessUnit ?: BusinessUnit::create([
            'department_id' => $mentor->department_id,
            'name' => $data['title'],
            'status' => 'open',
        ]);

        $unit->update([
            'name' => $data['title'],
            'description' => $data['problem'],
            'work_done' => $data['opportunity'],
            'function' => $data['purpose'] ?? $unit->function,
            'example_activities' => $data['expected_output'],
            'relevant_programs' => $data['needed_expertise'],
            'status' => 'open',
        ]);

        if (! $mentor->business_unit_id) {
            $mentor->update(['business_unit_id' => $unit->id]);
        }

        return response()->json($this->presenter->opportunity($unit->fresh(['department', 'mentors.user'])), 201);
    }

    public function show(Request $request, BusinessUnit $opportunity)
    {
        $user = $request->user();
        $participant = $user->participant;
        $match = $participant ? $this->matching->score($participant, $opportunity) : ['score' => 0];
        $application = $participant
            ? Application::where('participant_id', $participant->id)->where('business_unit_id', $opportunity->id)->latest('id')->first()
            : null;

        return response()->json($this->presenter->opportunity(
            $opportunity,
            $match,
            (bool) $application,
            $application,
        ));
    }
}
