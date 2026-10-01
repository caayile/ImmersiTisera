<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'program_id', 'evaluator_id', 'industry_understanding', 'relationship', 'output',
    'mutual_benefit', 'collaboration_potential', 'criteria', 'comments',
])]
class Evaluation extends Model
{
    protected function casts(): array
    {
        return [
            'criteria' => 'array',
        ];
    }

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
        if (is_array($this->criteria) && $this->criteria !== []) {
            return round(collect($this->criteria)->avg(fn (array $criterion) => (int) $criterion['score']), 1);
        }

        return round((
            $this->industry_understanding + $this->relationship + $this->output
            + $this->mutual_benefit + $this->collaboration_potential
        ) / 5, 1);
    }

    public function criteriaForDisplay(): array
    {
        return $this->criteria ?: [
            ['label' => 'Pemahaman Industri', 'score' => $this->industry_understanding],
            ['label' => 'Relasi', 'score' => $this->relationship],
            ['label' => 'Hasil', 'score' => $this->output],
            ['label' => 'Manfaat Bersama', 'score' => $this->mutual_benefit],
            ['label' => 'Potensi Kolaborasi', 'score' => $this->collaboration_potential],
        ];
    }
}
