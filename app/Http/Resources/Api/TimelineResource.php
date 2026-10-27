<?php

namespace App\Http\Resources\Api;

use App\Models\Timeline;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Timeline
 */
class TimelineResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program_id,
            'week' => $this->week,
            'phase' => $this->phase,
            'title' => $this->title,
            'description' => $this->description,
            'expected_output' => $this->expected_output,
            'evidence_link' => $this->evidence_link,
            'attachment_path' => $this->attachment_path,
            'mentor_note' => $this->mentor_note,
            'status' => $this->status,
        ];
    }
}
