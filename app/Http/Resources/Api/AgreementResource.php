<?php

namespace App\Http\Resources\Api;

use App\Models\Agreement;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Agreement
 */
class AgreementResource extends JsonResource
{
    public function toArray($request): array
    {
        $this->loadMissing([
            'program.participant.user',
            'program.mentor.user',
            'program.businessUnit',
        ]);
        $program = $this->program;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'letter_number' => $this->letter_number,
            'letter_issued_at' => optional($this->letter_issued_at)->toIso8601String(),
            'shared_goal' => $this->objective,
            'problem_opportunity' => $this->problem_statement,
            'primary_activity' => $this->activities,
            'supporting_activity' => null,
            'promised_output' => $this->main_output,
            'benefit_dosen' => $this->participant_benefit,
            'benefit_industry' => $this->business_benefit,
            'success_indicator' => collect($this->success_indicators ?? [])->implode(', '),
            'potential_collaboration' => $this->collaboration_potential,
            'participant_signature' => $this->participant_signature,
            'mentor_signature' => $this->mentor_signature,
            'business_unit' => optional($program?->businessUnit)->name,
            'dosen' => optional($program?->participant?->user) ? [
                'id' => $program->participant->user->id,
                'name' => $program->participant->user->name,
            ] : null,
            'mentor' => optional($program?->mentor?->user) ? [
                'id' => $program->mentor->user->id,
                'name' => $program->mentor->user->name,
                'phone' => $program->mentor->user->phone,
            ] : null,
            'program' => $program ? ['id' => $program->id] : null,
        ];
    }
}
