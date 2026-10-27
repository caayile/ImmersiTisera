<?php

namespace App\Http\Resources\Api;

use App\Models\DepartmentNeed;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DepartmentNeed
 */
class DepartmentNeedResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'prodi' => $this->prodi,
            'department' => $this->department,
            'purpose' => $this->purpose,
            'academic_needs' => $this->academic_needs,
            'problem' => $this->problem,
            'goal' => $this->goal,
            'created_by' => $this->created_by,
        ];
    }
}
