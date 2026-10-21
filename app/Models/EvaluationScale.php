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
            ['label' => 'Sangat Tidak Setuju', 'value' => 1],
            ['label' => 'Tidak Setuju', 'value' => 2],
            ['label' => 'Cukup', 'value' => 3],
            ['label' => 'Setuju', 'value' => 4],
            ['label' => 'Sangat Setuju', 'value' => 5],
        ];
    }

    /**
     * Label skala menurut nilai, dari data tersimpan atau bawaan.
     *
     * @return array<int, string>
     */
    public static function labelsByValue(): array
    {
        $stored = static::query()->orderBy('position')->get();

        if ($stored->isEmpty()) {
            return collect(static::defaultScales())
                ->mapWithKeys(fn (array $scale): array => [(int) $scale['value'] => $scale['label']])
                ->all();
        }

        return $stored
            ->mapWithKeys(fn (self $scale): array => [(int) $scale->value => $scale->label])
            ->all();
    }
}
