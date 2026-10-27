<?php

namespace App\Http\Resources\Api;

use App\Models\Evaluation;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Evaluation
 */
class EvaluationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program_id,
            'evaluator_id' => $this->evaluator_id,
            'industry_understanding' => $this->industry_understanding,
            'relationship' => $this->relationship,
            'output' => $this->output,
            'mutual_benefit' => $this->mutual_benefit,
            'collaboration_potential' => $this->collaboration_potential,
            'criteria' => $this->criteria,
            'grade_groups' => $this->grade_groups,
            'comments' => $this->comments,
            'average' => $this->average(),
            'predicate' => $this->predicate(),
            'predicate_label' => $this->predicateLabel($this->predicate()),
        ];
    }
}
