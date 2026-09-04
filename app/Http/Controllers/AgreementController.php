<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Program;
use Illuminate\Http\Request;

class AgreementController extends Controller
{
    public function show(Request $request, Agreement $agreement)
    {
        $this->authorizeAccess($request, $agreement);

        return response()->json($agreement->load(['dosen.dosenProfile', 'mentor.mentorProfile', 'opportunity', 'program']));
    }

    public function update(Request $request, Agreement $agreement)
    {
        $this->authorizeAccess($request, $agreement);

        if (! in_array($agreement->status, ['draft', 'waiting_mentor', 'waiting_dosen'], true)) {
            return response()->json(['message' => 'Agreement yang sudah confirmed tidak bisa diubah.'], 422);
        }

        $data = $request->validate([
            'shared_goal' => ['sometimes', 'string'],
            'problem_opportunity' => ['sometimes', 'string'],
            'primary_activity' => ['sometimes', 'in:penugasan,observasi,riset'],
            'supporting_activity' => ['nullable', 'in:penugasan,observasi,riset'],
            'promised_output' => ['sometimes', 'string'],
            'benefit_dosen' => ['sometimes', 'string'],
            'benefit_industry' => ['sometimes', 'string'],
            'success_indicator' => ['sometimes', 'string'],
            'potential_collaboration' => ['nullable', 'string'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date'],
        ]);

        $agreement->update([
            ...$data,
            'status' => 'draft',
            'mentor_approved_at' => null,
            'dosen_approved_at' => null,
        ]);

        return response()->json($agreement->fresh(['dosen', 'mentor', 'opportunity']));
    }

    public function approve(Request $request, Agreement $agreement)
    {
        $this->authorizeAccess($request, $agreement);
        $user = $request->user();

        if ($user->isMentor()) {
            $agreement->update([
                'mentor_approved_at' => now(),
                'status' => $agreement->dosen_approved_at ? 'agreed' : 'waiting_dosen',
            ]);
        } elseif ($user->isUser()) {
            if (! $agreement->mentor_approved_at) {
                return response()->json(['message' => 'Menunggu persetujuan mentor terlebih dahulu.'], 422);
            }

            $agreement->update([
                'dosen_approved_at' => now(),
                'status' => 'agreed',
            ]);
        }

        $agreement->refresh();

        if ($agreement->status === 'agreed' && ! $agreement->program) {
            $start = $agreement->period_start ?? now();
            $end = $agreement->period_end ?? now()->addDays(60);

            $program = Program::create([
                'agreement_id' => $agreement->id,
                'dosen_id' => $agreement->dosen_id,
                'mentor_id' => $agreement->mentor_id,
                'opportunity_id' => $agreement->opportunity_id,
                'status' => 'active',
                'current_phase' => 'discover',
                'start_date' => $start,
                'end_date' => $end,
                'maturity_level' => 1,
            ]);

            $agreement->update(['status' => 'active']);

            return response()->json($agreement->fresh(['program', 'dosen', 'mentor', 'opportunity']));
        }

        return response()->json($agreement->fresh(['program', 'dosen', 'mentor', 'opportunity']));
    }

    private function authorizeAccess(Request $request, Agreement $agreement): void
    {
        $user = $request->user();
        $allowed = $user->isAdmin()
            || $agreement->dosen_id === $user->id
            || $agreement->mentor_id === $user->id;

        abort_unless($allowed, 403);
    }
}
