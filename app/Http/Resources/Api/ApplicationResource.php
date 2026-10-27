<?php

namespace App\Http\Resources\Api;

use App\Models\Application;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Application
 */
class ApplicationResource extends JsonResource
{
    public function toArray($request): array
    {
        $this->loadMissing([
            'participant.user',
            'businessUnit.department',
            'mentor.user',
            'program.agreement',
        ]);

        return [
            'id' => $this->id,
            'status' => $this->status,
            'batch' => $this->batch,
            'letter_number' => $this->letter_number,
            'department' => optional($this->businessUnit?->department) ? [
                'id' => $this->businessUnit->department->id,
                'name' => $this->businessUnit->department->name,
            ] : null,
            'business_unit' => optional($this->businessUnit) ? [
                'id' => $this->businessUnit->id,
                'name' => $this->businessUnit->name,
            ] : null,
            'mentor' => optional($this->mentor?->user) ? [
                'id' => $this->mentor->user->id,
                'name' => $this->mentor->user->name,
            ] : null,
            'match_score' => $this->match_score,
            'match_label' => $this->matchLabel((int) $this->match_score),
            'primary_activity' => $this->normalizedActivityTypes()[0] ?? null,
            'supporting_activity' => $this->normalizedActivityTypes()[1] ?? null,
            'proposed_shared_goal' => $this->shared_goal,
            'shared_goal' => $this->shared_goal,
            'activity_types' => $this->normalizedActivityTypes(),
            'problem_statement' => $this->problem_statement,
            'main_output' => $this->main_output,
            'participant_benefit' => $this->participant_benefit,
            'business_benefit' => $this->business_benefit,
            'success_indicators' => $this->normalizedSuccessIndicators(),
            'indicator_feedback' => $this->indicator_feedback,
            'mentor_note' => $this->mentor_note,
            'dosen' => optional($this->participant?->user) ? [
                'id' => $this->participant->user->id,
                'name' => $this->participant->user->name,
                'email' => $this->participant->user->email,
            ] : null,
            'opportunity' => optional($this->businessUnit) ? [
                'id' => $this->businessUnit->id,
                'title' => $this->businessUnit->name,
            ] : null,
            'agreement' => optional($this->program?->agreement)->id ? [
                'id' => $this->program->agreement->id,
            ] : null,
        ];
    }

    private function matchLabel(int $score): string
    {
        return match (true) {
            $score >= 70 => 'Sangat relevan',
            $score >= 50 => 'Cukup relevan',
            default => 'Kurang relevan',
        };
    }
}
