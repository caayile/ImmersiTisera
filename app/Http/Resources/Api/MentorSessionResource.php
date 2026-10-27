<?php

namespace App\Http\Resources\Api;

use App\Models\MentorSession;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MentorSession
 */
class MentorSessionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program_id,
            'week' => $this->week,
            'session_date' => optional($this->session_date)->toDateString(),
            'found' => $this->findings,
            'working_on' => $this->current_work,
            'next_action' => $this->next_action,
            'mentor_feedback' => $this->feedback,
        ];
    }
}
