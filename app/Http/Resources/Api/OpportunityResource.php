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
        $this->loadMissing(['department', 'mentors.user']);
        $mentor = $this->mentors->first();

        return [
            'id' => $this->id,
            'title' => $this->name,
            'field' => optional($this->department)->name,
            'business_unit' => $this->name,
            'purpose' => $this->function ?: 'pembelajaran',
            'problem' => $this->description,
            'opportunity' => $this->work_done ?: $this->function,
            'needed_expertise' => $this->relevant_programs ?? [],
            'expected_output' => $this->example_activities,
            'status' => $this->isOpen() ? 'open' : 'closed',
            'mentor' => optional($mentor?->user) ? [
                'id' => $mentor->user->id,
                'name' => $mentor->user->name,
                'company' => optional($this->department)->name,
            ] : null,
        ];
    }
}
