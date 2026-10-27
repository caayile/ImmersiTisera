<?php

namespace App\Http\Resources\Api;

use App\Models\Program;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Program
 */
class ProgramResource extends JsonResource
{
    public function toArray($request): array
    {
        $this->loadMissing([
            'participant.user',
            'mentor.user',
            'businessUnit',
            'agreement',
            'collaboration',
        ]);

        $avg = $this->relationLoaded('evaluations') && $this->evaluations->isNotEmpty()
            ? round($this->evaluations->avg(fn ($item) => $item->average()), 1)
            : null;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'current_week' => $this->current_week,
            'computed_phase' => $this->phase((int) $this->current_week),
            'progress' => $this->progress,
            'collaboration_score' => $avg,
            'connect_decision' => optional($this->collaboration)->collaboration_type,
            'industry_insight' => optional($this->agreement)->problem_statement,
            'problem_statement' => optional($this->agreement)->problem_statement,
            'contribution_notes' => optional($this->agreement)->activities,
            'dosen' => optional($this->participant?->user) ? [
                'id' => $this->participant->user->id,
                'name' => $this->participant->user->name,
                'email' => $this->participant->user->email,
            ] : null,
            'mentor' => optional($this->mentor?->user) ? [
                'id' => $this->mentor->user->id,
                'name' => $this->mentor->user->name,
                'email' => $this->mentor->user->email,
            ] : null,
            'opportunity' => optional($this->businessUnit) ? [
                'id' => $this->businessUnit->id,
                'title' => $this->businessUnit->name,
            ] : null,
            'agreement' => $this->agreement ? new AgreementResource($this->agreement) : null,
        ];
    }

    private function phase(int $week): string
    {
        return match (true) {
            $week <= 2 => 'discover',
            $week <= 4 => 'understand',
            $week <= 7 => 'contribute',
            default => 'deliver',
        };
    }
}
