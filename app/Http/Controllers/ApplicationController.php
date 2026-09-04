<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Application;
use App\Models\Opportunity;
use App\Services\MatchingService;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Application::with(['dosen.dosenProfile', 'opportunity.mentor.mentorProfile', 'agreement'])->latest();

        if ($user->isUser()) {
            $query->where('dosen_id', $user->id);
        } elseif ($user->isMentor()) {
            $query->whereHas('opportunity', fn ($q) => $q->where('mentor_id', $user->id));
        }

        return response()->json($query->get());
    }

    public function store(Request $request, MatchingService $matching)
    {
        $data = $request->validate([
            'opportunity_id' => ['required', 'exists:opportunities,id'],
            'primary_activity' => ['required', 'in:penugasan,observasi,riset'],
            'supporting_activity' => ['nullable', 'in:penugasan,observasi,riset'],
            'proposed_shared_goal' => ['required', 'string'],
        ]);

        $user = $request->user();
        $opportunity = Opportunity::findOrFail($data['opportunity_id']);

        $existing = Application::where('dosen_id', $user->id)
            ->where('opportunity_id', $opportunity->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Anda sudah mengajukan minat pada opportunity ini.'], 422);
        }

        $match = $user->dosenProfile
            ? $matching->score($user->dosenProfile, $opportunity)
            : ['score' => 0, 'label' => 'Kurang Relevan'];

        $application = Application::create([
            'dosen_id' => $user->id,
            'opportunity_id' => $opportunity->id,
            'match_score' => $match['score'],
            'match_label' => $match['label'],
            'primary_activity' => $data['primary_activity'],
            'supporting_activity' => $data['supporting_activity'] ?? null,
            'proposed_shared_goal' => $data['proposed_shared_goal'],
            'status' => 'pending',
        ]);

        return response()->json($application->load(['opportunity.mentor.mentorProfile']), 201);
    }

    public function review(Request $request, Application $application)
    {
        abort_unless($application->opportunity->mentor_id === $request->user()->id, 403);

        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'mentor_note' => ['nullable', 'string'],
        ]);

        $application->update([
            'status' => $data['decision'],
            'mentor_note' => $data['mentor_note'] ?? $application->mentor_note,
        ]);

        if ($data['decision'] === 'approved' && ! $application->agreement) {
            $opportunity = $application->opportunity;
            $dosen = $application->dosen->dosenProfile;
            $mentor = $request->user()->mentorProfile;

            Agreement::create([
                'application_id' => $application->id,
                'dosen_id' => $application->dosen_id,
                'mentor_id' => $request->user()->id,
                'opportunity_id' => $opportunity->id,
                'prodi' => $dosen?->prodi,
                'business_unit' => $opportunity->business_unit,
                'department_function' => $mentor?->department_function,
                'period_start' => $opportunity->timeline_start ?? now()->toDateString(),
                'period_end' => $opportunity->timeline_end ?? now()->addDays(60)->toDateString(),
                'shared_goal' => $application->proposed_shared_goal,
                'problem_opportunity' => trim(($opportunity->problem ?? '')."\n\n".($opportunity->opportunity ?? '')),
                'primary_activity' => $application->primary_activity,
                'supporting_activity' => $application->supporting_activity,
                'promised_output' => $opportunity->expected_output,
                'benefit_dosen' => 'Industry knowledge, teaching case, competency development',
                'benefit_industry' => $mentor?->industry_needs,
                'success_indicator' => 'Shared goal tercapai, output disepakati, dan ada potensi kolaborasi lanjutan.',
                'timeline' => $this->defaultTimeline(),
                'status' => 'draft',
            ]);
        }

        return response()->json($application->fresh(['agreement', 'dosen.dosenProfile', 'opportunity']));
    }

    private function defaultTimeline(): array
    {
        return [
            ['week' => '1-2', 'phase' => 'discover', 'focus' => 'Orientation, observasi unit bisnis, industry insight'],
            ['week' => '3-4', 'phase' => 'understand', 'focus' => 'Problem statement & reality check'],
            ['week' => '5-7', 'phase' => 'contribute', 'focus' => 'Try, test, develop + weekly checkpoint'],
            ['week' => '8', 'phase' => 'deliver', 'focus' => 'Final output & presentation'],
        ];
    }
}
