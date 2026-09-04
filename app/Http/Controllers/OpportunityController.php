<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Opportunity;
use App\Services\MatchingService;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function index(Request $request, MatchingService $matching)
    {
        $user = $request->user();
        $query = Opportunity::with(['mentor.mentorProfile'])->latest();

        if ($user->isMentor()) {
            $query->where('mentor_id', $user->id);
        } else {
            $query->where('status', 'open');
        }

        $items = $query->get()->map(function (Opportunity $opportunity) use ($user, $matching) {
            $payload = $this->present($opportunity);

            if ($user->isUser() && $user->dosenProfile) {
                $match = $matching->score($user->dosenProfile, $opportunity);
                $payload['match_score'] = $match['score'];
                $payload['match_label'] = $match['label'];
                $payload['applied'] = Application::where('dosen_id', $user->id)
                    ->where('opportunity_id', $opportunity->id)
                    ->exists();
            }

            return $payload;
        });

        if ($user->isUser()) {
            $items = $items->sortByDesc('match_score')->values();
        }

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['mentor_id'] = $request->user()->id;
        $data['status'] = 'open';

        $opportunity = Opportunity::create($data);

        return response()->json($this->present($opportunity->load('mentor.mentorProfile')), 201);
    }

    public function show(Request $request, Opportunity $opportunity, MatchingService $matching)
    {
        $opportunity->load(['mentor.mentorProfile']);
        $payload = $this->present($opportunity);
        $user = $request->user();

        if ($user->isUser() && $user->dosenProfile) {
            $match = $matching->score($user->dosenProfile, $opportunity);
            $payload['match_score'] = $match['score'];
            $payload['match_label'] = $match['label'];
            $payload['application'] = Application::with('agreement')
                ->where('dosen_id', $user->id)
                ->where('opportunity_id', $opportunity->id)
                ->first();
        }

        return response()->json($payload);
    }

    public function update(Request $request, Opportunity $opportunity)
    {
        abort_unless($opportunity->mentor_id === $request->user()->id || $request->user()->isAdmin(), 403);
        $opportunity->update($this->validated($request, false));

        return response()->json($this->present($opportunity->fresh('mentor.mentorProfile')));
    }

    private function validated(Request $request, bool $required = true): array
    {
        $rule = $required ? 'required' : 'sometimes';

        return $request->validate([
            'title' => [$rule, 'string', 'max:180'],
            'field' => [$rule, 'string', 'max:120'],
            'business_unit' => [$rule, 'string', 'max:120'],
            'purpose' => [$rule, 'in:riset,observasi,pembelajaran,penugasan'],
            'problem' => [$rule, 'string'],
            'opportunity' => [$rule, 'string'],
            'needed_expertise' => [$rule, 'array', 'min:1'],
            'expected_output' => [$rule, 'string'],
            'allowed_activities' => ['nullable', 'array'],
            'timeline_start' => ['nullable', 'date'],
            'timeline_end' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:open,closed'],
        ]);
    }

    private function present(Opportunity $opportunity): array
    {
        return [
            ...$opportunity->toArray(),
            'mentor' => [
                'id' => $opportunity->mentor?->id,
                'name' => $opportunity->mentor?->name,
                'company' => $opportunity->mentor?->mentorProfile?->company_name,
                'job_title' => $opportunity->mentor?->mentorProfile?->job_title,
            ],
        ];
    }
}
