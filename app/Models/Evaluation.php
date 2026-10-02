<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'program_id', 'evaluator_id', 'industry_understanding', 'relationship', 'output',
    'mutual_benefit', 'collaboration_potential', 'criteria', 'grade_groups', 'comments',
])]
class Evaluation extends Model
{
    protected function casts(): array
    {
        return [
            'criteria' => 'array',
            'grade_groups' => 'array',
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

    /**
     * Kelompok nilai raport bawaan: Project (bobot 40%) + Sikap (bobot 60%).
     *
     * @return array<int, array{name: string, weight: int, aspects: array<int, array{label: string, score: ?int}>}>
     */
    public static function defaultGradeGroups(): array
    {
        return [
            [
                'name' => 'Project',
                'weight' => 40,
                'aspects' => [
                    ['label' => 'Kualitas Hasil Kerja', 'score' => null],
                    ['label' => 'Ketepatan Waktu', 'score' => null],
                    ['label' => 'Penguasaan Teknis', 'score' => null],
                ],
            ],
            [
                'name' => 'Sikap',
                'weight' => 60,
                'aspects' => [
                    ['label' => 'Kehadiran', 'score' => null],
                    ['label' => 'Kedisiplinan', 'score' => null],
                    ['label' => 'Tanggung Jawab', 'score' => null],
                    ['label' => 'Kerja Sama', 'score' => null],
                    ['label' => 'Inisiatif', 'score' => null],
                ],
            ],
        ];
    }

    public function hasReport(): bool
    {
        return is_array($this->grade_groups) && $this->grade_groups !== [];
    }

    /**
     * Rata-rata raport 0–100: rata-rata tiap kelompok dibobot, lalu dibagi
     * total bobot (aman bila bobot tidak genap 100). Null bila tak ada skor.
     */
    public function reportAverage(): ?float
    {
        if (! $this->hasReport()) {
            return null;
        }

        $weightedSum = 0.0;
        $weightTotal = 0.0;

        foreach ($this->grade_groups as $group) {
            $scores = collect($group['aspects'] ?? [])
                ->map(fn ($aspect) => $aspect['score'] ?? null)
                ->filter(fn ($score) => $score !== null && $score !== '')
                ->map(fn ($score) => (float) $score);

            if ($scores->isEmpty()) {
                continue;
            }

            $weight = (float) ($group['weight'] ?? 0);
            $weightedSum += $scores->avg() * $weight;
            $weightTotal += $weight;
        }

        if ($weightTotal <= 0) {
            return null;
        }

        return round($weightedSum / $weightTotal, 1);
    }

    /**
     * Predikat raport ala Indonesia: A ≥85, B ≥70, C ≥60, D ≥50, E <50.
     */
    public function predicate(): ?string
    {
        $average = $this->reportAverage();

        if ($average === null) {
            return null;
        }

        return match (true) {
            $average >= 85 => 'A',
            $average >= 70 => 'B',
            $average >= 60 => 'C',
            $average >= 50 => 'D',
            default => 'E',
        };
    }

    public function average(): float
    {
        if (is_array($this->criteria) && $this->criteria !== []) {
            return round(collect($this->criteria)->avg(fn (array $criterion) => (int) $criterion['score']), 1);
        }

        if ($this->industry_understanding || $this->relationship || $this->output || $this->mutual_benefit || $this->collaboration_potential) {
            return round((
                $this->industry_understanding + $this->relationship + $this->output
                + $this->mutual_benefit + $this->collaboration_potential
            ) / 5, 1);
        }

        // Evaluasi raport murni (tanpa data 1–5): konversi ke skala 5 agar
        // agregat yang memakai average() tetap koheren.
        if ($this->reportAverage() !== null) {
            return round($this->reportAverage() / 20, 1);
        }

        return 0.0;
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
