<?php

namespace App\Http\Resources\Api;

use App\Models\BusinessUnit;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BusinessUnit
 */
class OpportunityResource extends JsonResource
{
    public function toArray($request): array
    {
        $usesInlineData = $this->resource->hasAttribute('department_name');

        if (! $usesInlineData) {
            $this->loadMissing(['department', 'mentors.user']);
        }

        $mentor = $usesInlineData ? null : $this->mentors->first();
        $departmentName = $usesInlineData
            ? $this->department_name
            : optional($this->department)->name;
        $user = optional($request->user());
        $hasAppliedFlag = $this->resource->hasAttribute('applied_by_user');
        $applied = $hasAppliedFlag ? (bool) $this->applied_by_user : false;

        // Fallback untuk endpoint show yang memuat satu unit tanpa preload.
        if (! $hasAppliedFlag && $user && $user->participant) {
            $applied = $this->applications()
                ->where('participant_id', $user->participant->id)
                ->exists();
        }

        $score = $this->calculateMatchScore($user);

        if ($usesInlineData) {
            $mentorData = $this->mentor_name === null
                ? null
                : [
                    'id' => $this->mentor_user_id,
                    'name' => $this->mentor_name,
                    'company' => $departmentName,
                ];
        } else {
            $mentorData = $mentor?->user ? [
                'id' => $mentor->user->id,
                'name' => $mentor->user->name,
                'company' => $departmentName,
            ] : null;
        }

        return [
            'id' => $this->id,
            'title' => $this->name,
            'field' => $departmentName,
            'business_unit' => $this->name,
            'purpose' => $this->function ?: 'pembelajaran',
            'problem' => $this->description,
            'opportunity' => $this->work_done ?: $this->function,
            'needed_expertise' => $this->relevant_programs ?? [],
            'expected_output' => $this->example_activities,
            'status' => $this->isOpen() ? 'open' : 'closed',
            'match_score' => $score,
            'match_label' => $this->matchLabel($score),
            'applied' => $applied,
            'mentor' => $mentorData,
        ];
    }

    private function calculateMatchScore(?object $user): int
    {
        if (! $user || ! $user->participant) {
            return 0;
        }

        $relevant = collect($this->relevant_programs)->map(fn ($p) => strtolower((string) $p));
        $userSkills = collect($user->participant->skills ?? [])->map(fn ($s) => strtolower((string) $s));

        if ($relevant->isEmpty() || $userSkills->isEmpty()) {
            return 0;
        }

        $overlap = $relevant->filter(fn ($skill) => $userSkills->contains($skill))->count();

        return (int) round(($overlap / $relevant->count()) * 100);
    }

    private function matchLabel(int $score): string
    {
        if ($score >= 75) {
            return 'Sangat Cocok';
        }
        if ($score >= 50) {
            return 'Cocok';
        }
        if ($score >= 25) {
            return 'Cukup Cocok';
        }

        return 'Kurang Cocok';
    }
}
