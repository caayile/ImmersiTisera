<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Support\ApiPresenter;
use Illuminate\Http\Request;

class AgreementController extends Controller
{
    public function __construct(private ApiPresenter $presenter) {}

    public function show(Request $request, Agreement $agreement)
    {
        $this->authorizeAccess($request, $agreement);

        return response()->json($this->presenter->agreement($agreement));
    }

    public function update(Request $request, Agreement $agreement)
    {
        $this->authorizeAccess($request, $agreement);

        if (! in_array($agreement->status, ['draft', 'submitted', 'revision'], true)) {
            return response()->json(['message' => 'Perjanjian yang sudah disepakati tidak bisa diubah.'], 422);
        }

        $data = $request->validate([
            'shared_goal' => ['sometimes', 'string'],
            'problem_opportunity' => ['sometimes', 'string'],
            'primary_activity' => ['sometimes', 'string'],
            'supporting_activity' => ['nullable', 'string'],
            'promised_output' => ['sometimes', 'string'],
            'benefit_dosen' => ['sometimes', 'string'],
            'benefit_industry' => ['sometimes', 'string'],
            'success_indicator' => ['sometimes', 'string'],
            'potential_collaboration' => ['nullable', 'string'],
            'participant_signature' => [
                'required',
                'string',
                'regex:/^data:image\/(png|jpeg|jpg|webp);base64,/i',
            ],
        ]);

        $indicators = isset($data['success_indicator'])
            ? array_values(array_filter(array_map('trim', explode(',', $data['success_indicator']))))
            : $agreement->success_indicators;

        $agreement->update([
            'objective' => $data['shared_goal'] ?? $agreement->objective,
            'problem_statement' => $data['problem_opportunity'] ?? $agreement->problem_statement,
            'activities' => trim(($data['primary_activity'] ?? $agreement->activities).' '.($data['supporting_activity'] ?? '')),
            'main_output' => $data['promised_output'] ?? $agreement->main_output,
            'participant_benefit' => $data['benefit_dosen'] ?? $agreement->participant_benefit,
            'business_benefit' => $data['benefit_industry'] ?? $agreement->business_benefit,
            'success_indicators' => $indicators,
            'collaboration_potential' => $data['potential_collaboration'] ?? $agreement->collaboration_potential,
            'status' => 'draft',
            'participant_approved_at' => null,
            'mentor_approved_at' => null,
            'participant_signature' => $data['participant_signature'],
            'mentor_signature' => null,
        ]);

        return response()->json($this->presenter->agreement($agreement->fresh()));
    }

    public function approve(Request $request, Agreement $agreement)
    {
        $this->authorizeAccess($request, $agreement);
        $user = $request->user();
        $program = $agreement->program;

        $signature = $request->validate([
            'signature' => [
                'required',
                'string',
                'regex:/^data:image\/(png|jpeg|jpg|webp);base64,/i',
            ],
        ])['signature'];

        if ($user->isMentor()) {
            $agreement->update([
                'mentor_approved_at' => now(),
                'status' => $agreement->participant_approved_at ? 'agreed' : 'submitted',
                'mentor_signature' => $signature,
            ]);
        } elseif ($user->isParticipant()) {
            $agreement->update([
                'participant_approved_at' => now(),
                'status' => $agreement->mentor_approved_at ? 'agreed' : 'submitted',
                'participant_signature' => $signature,
            ]);
        }

        $agreement->refresh();

        if ($agreement->status === 'agreed') {
            app(\App\Services\AgreementLetterService::class)->issue($agreement);
            $agreement->refresh();
        }

        if ($agreement->status === 'agreed' && $program) {
            $program->update([
                'status' => 'active',
                'start_date' => $program->start_date ?: now()->toDateString(),
                'end_date' => $program->end_date ?: now()->addDays(60)->toDateString(),
            ]);
            $program->seedTimeline();
        }

        return response()->json($this->presenter->agreement($agreement->fresh(['program'])));
    }

    private function authorizeAccess(Request $request, Agreement $agreement): void
    {
        $user = $request->user();
        $program = $agreement->program;

        $allowed = $user->isAdmin()
            || $program?->participant?->user_id === $user->id
            || $program?->mentor?->user_id === $user->id;

        abort_unless($allowed, 403);
    }
}
