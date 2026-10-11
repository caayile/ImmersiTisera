<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['label', 'value', 'position'])]
class EvaluationScale extends Model
{
    /**
     * Skala Likert bawaan 1–5, dipakai seeder dan fallback.
     *
     * @return array<int, array{label: string, value: int}>
     */
    public static function defaultScales(): array
    {
        return [
            ['label' => 'Sangat Kurang', 'value' => 1],
            ['label' => 'Kurang', 'value' => 2],
            ['label' => 'Cukup', 'value' => 3],
            ['label' => 'Baik', 'value' => 4],
            ['label' => 'Sangat Baik', 'value' => 5],
        ];
    }
}
