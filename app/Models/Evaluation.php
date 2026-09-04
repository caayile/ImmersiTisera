<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'program_id', 'evaluator_id', 'industry_understanding', 'relationship', 'output',
    'mutual_benefit', 'collaboration_potential', 'comments',
])]
class Evaluation extends Model
{
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function average(): float
    {
        return round((
            $this->industry_understanding + $this->relationship + $this->output
            + $this->mutual_benefit + $this->collaboration_potential
        ) / 5, 1);
    }
}
