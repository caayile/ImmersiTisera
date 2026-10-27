<?php

namespace App\Http\Resources\Api;

use App\Models\Logbook;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Logbook
 */
class LogbookResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'entry_date' => optional($this->date)->toDateString(),
            'what_did' => $this->what_i_did,
            'what_learned' => $this->what_i_learned,
            'what_found' => $this->what_i_found,
            'obstacles' => $this->value,
            'output' => $this->next_action,
            'activity' => $this->activity,
            'mentor_verified' => $this->status === 'approved',
            'status' => $this->status,
        ];
    }
}
