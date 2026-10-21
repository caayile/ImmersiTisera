<?php

namespace App\Services;

use App\Models\Evaluation;
use App\Models\EvaluationQuestion;
use App\Models\EvaluationScale;
use App\Models\EvaluationSection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class QuestionnaireReport
{
    /**
     * Ringkasan kuesioner peserta, sudah disaring menurut program, departemen, dan mentor.
     *
     * @return array{
     *     respondents: int,
     *     overall: ?float,
     *     scale_max: int,
     *     scales: list<array{label: string, value: int}>,
     *     sections: list<array{key: string, label: string, average: ?float}>,
     *     questions: list<array{section: string, label: string, average: float, respondents: int}>,
     *     lowest: ?array{section: string, label: string, average: float},
     *     rows: list<array{participant: string, unit_bisnis: string, departemen: string, mentor: string, scores: array{program: ?float, departemen: ?float, mentor: ?float}, average: float, comments: ?string, answers: list<array{section: string, label: string, score: float}>}>,
     *     by_program: list<array{label: string, average: float}>,
     *     by_departemen: list<array{label: string, average: float}>,
     *     by_mentor: list<array{label: string, average: float}>
     * }
     */
    public function summarize(?int $programId, ?int $businessUnitId, ?int $mentorId): array
    {
        $catalog = $this->sectionCatalog();
        $evaluations = $this->questionnaires($programId, $businessUnitId, $mentorId);
        $questions = $this->questionAverages($evaluations, $catalog);
        $scales = $this->scales();

        return [
            'respondents' => $evaluations->count(),
            'overall' => $this->pooledAverage($evaluations, $catalog),
            'scale_max' => (int) (collect($scales)->max('value') ?: 5),
            'scales' => $scales,
            'sections' => $this->sectionAverages($evaluations, $catalog),
            'questions' => $questions,
            'lowest' => $this->lowestQuestion($questions),
            'rows' => $this->rows($evaluations, $catalog),
            'by_program' => $this->breakdown(
                $evaluations,
                $catalog,
                fn (Evaluation $evaluation): ?int => $evaluation->program_id,
                fn (Evaluation $evaluation): string => ($evaluation->program?->participant?->user?->name ?? 'Program')
                    .' — '.($evaluation->program?->businessUnit?->name ?? '—'),
            ),
            'by_departemen' => $this->breakdown(
                $evaluations,
                $catalog,
                fn (Evaluation $evaluation): ?int => $evaluation->program?->business_unit_id,
                fn (Evaluation $evaluation): string => $evaluation->program?->businessUnit?->name ?? '—',
            ),
            'by_mentor' => $this->breakdown(
                $evaluations,
                $catalog,
                fn (Evaluation $evaluation): ?int => $evaluation->program?->mentor_id,
                fn (Evaluation $evaluation): string => $evaluation->program?->mentor?->user?->name ?? '—',
            ),
        ];
    }

    /**
     * @return Collection<int, Evaluation>
     */
    private function questionnaires(?int $programId, ?int $businessUnitId, ?int $mentorId): Collection
    {
        return Evaluation::query()
            ->with([
                'program.participant.user',
                'program.department',
                'program.businessUnit',
                'program.mentor.user',
                'evaluator',
            ])
            ->whereHas('evaluator', function (Builder $query): void {
                $query->whereIn('role', ['participant', 'user']);
            })
            ->when($programId, fn (Builder $query) => $query->where('program_id', $programId))
            ->when($businessUnitId || $mentorId, function (Builder $query) use ($businessUnitId, $mentorId): void {
                $query->whereHas('program', function (Builder $program) use ($businessUnitId, $mentorId): void {
                    $program
                        ->when($businessUnitId, fn (Builder $inner) => $inner->where('business_unit_id', $businessUnitId))
                        ->when($mentorId, fn (Builder $inner) => $inner->where('mentor_id', $mentorId));
                });
            })
            ->latest()
            ->get()
            ->filter(fn (Evaluation $evaluation): bool => $this->criteriaItems($evaluation) !== [])
            ->values();
    }

    /**
     * Peta pertanyaan ke bagian kuesioner (program, departemen, mentor).
     *
     * @return array<string, array{label: string, order: array<string, int>}>
     */
    private function sectionCatalog(): array
    {
        $sections = EvaluationSection::query()
            ->with(['questions' => fn (Relation $query) => $query->where('is_active', true)->orderBy('position')])
            ->orderBy('position')
            ->get();

        $bank = $sections->isNotEmpty()
            ? $sections->map(fn (EvaluationSection $section): array => [
                'key' => $section->key,
                'title' => $section->title,
                'questions' => $section->questions->pluck('question')->all(),
            ])->all()
            : EvaluationQuestion::defaultBank();

        $catalog = [];
        $position = 0;

        foreach ($bank as $section) {
            $key = (string) $section['key'];
            $order = [];

            foreach ($section['questions'] as $question) {
                $order[trim((string) $question)] = $position;
                $position++;
            }

            $catalog[$key] = [
                'label' => match ($key) {
                    'program' => 'Program',
                    'departemen' => 'Departemen',
                    'mentor' => 'Mentor',
                    default => (string) $section['title'],
                },
                'order' => $order,
            ];
        }

        return $catalog;
    }

    /**
     * @param  array<string, array{label: string, order: array<string, int>}>  $catalog
     * @return list<array{label: string, score: float, key: string, section: string, order: int}>
     */
    private function scoredCriteria(Evaluation $evaluation, array $catalog): array
    {
        $items = [];

        foreach ($this->criteriaItems($evaluation) as $criterion) {
            if (! is_array($criterion)) {
                continue;
            }

            $score = $criterion['score'] ?? null;

            if ($score === null || $score === '') {
                continue;
            }

            $label = trim((string) ($criterion['label'] ?? ''));

            if ($label === '') {
                continue;
            }

            $located = $this->locate($label, $catalog);
            $items[] = [
                'label' => $label,
                'score' => (float) $score,
                'key' => $located['key'],
                'section' => $located['label'],
                'order' => $located['order'],
            ];
        }

        return $items;
    }

    /**
     * @param  array<string, array{label: string, order: array<string, int>}>  $catalog
     * @return array{key: string, label: string, order: int}
     */
    private function locate(string $label, array $catalog): array
    {
        foreach ($catalog as $key => $section) {
            if (array_key_exists($label, $section['order'])) {
                return [
                    'key' => $key,
                    'label' => $section['label'],
                    'order' => $section['order'][$label],
                ];
            }
        }

        return ['key' => 'lainnya', 'label' => 'Jawaban yang diisi', 'order' => 100000];
    }

    /**
     * @param  list<float>  $scores
     */
    private function average(array $scores): ?float
    {
        if ($scores === []) {
            return null;
        }

        return round(array_sum($scores) / count($scores), 1);
    }

    /**
     * @param  Collection<int, Evaluation>  $evaluations
     * @param  array<string, array{label: string, order: array<string, int>}>  $catalog
     */
    private function pooledAverage(Collection $evaluations, array $catalog): ?float
    {
        $scores = [];

        foreach ($evaluations as $evaluation) {
            foreach ($this->scoredCriteria($evaluation, $catalog) as $item) {
                $scores[] = $item['score'];
            }
        }

        return $this->average($scores);
    }

    /**
     * @param  Collection<int, Evaluation>  $evaluations
     * @param  array<string, array{label: string, order: array<string, int>}>  $catalog
     * @return list<array{key: string, label: string, average: ?float}>
     */
    private function sectionAverages(Collection $evaluations, array $catalog): array
    {
        $scores = [];

        foreach ($evaluations as $evaluation) {
            foreach ($this->scoredCriteria($evaluation, $catalog) as $item) {
                $scores[$item['key']][] = $item['score'];
            }
        }

        $sections = [];

        foreach (['program' => 'Program', 'departemen' => 'Departemen', 'mentor' => 'Mentor'] as $key => $fallback) {
            $sections[] = [
                'key' => $key,
                'label' => $catalog[$key]['label'] ?? $fallback,
                'average' => $this->average($scores[$key] ?? []),
            ];
        }

        if (isset($scores['lainnya'])) {
            $sections[] = [
                'key' => 'lainnya',
                'label' => 'Jawaban yang diisi',
                'average' => $this->average($scores['lainnya']),
            ];
        }

        return $sections;
    }

    /**
     * @param  Collection<int, Evaluation>  $evaluations
     * @param  array<string, array{label: string, order: array<string, int>}>  $catalog
     * @return list<array{section: string, label: string, average: float, respondents: int}>
     */
    private function questionAverages(Collection $evaluations, array $catalog): array
    {
        $buckets = [];

        foreach ($evaluations as $evaluation) {
            foreach ($this->scoredCriteria($evaluation, $catalog) as $item) {
                $buckets[$item['label']] ??= [
                    'section' => $item['section'],
                    'label' => $item['label'],
                    'order' => $item['order'],
                    'scores' => [],
                ];
                $buckets[$item['label']]['scores'][] = $item['score'];
            }
        }

        return collect($buckets)
            ->sortBy('order')
            ->map(fn (array $bucket): array => [
                'section' => $bucket['section'],
                'label' => $bucket['label'],
                'average' => $this->average($bucket['scores']) ?? 0.0,
                'respondents' => count($bucket['scores']),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{section: string, label: string, average: float, respondents: int}>  $questions
     * @return ?array{section: string, label: string, average: float}
     */
    private function lowestQuestion(array $questions): ?array
    {
        if ($questions === []) {
            return null;
        }

        $lowest = collect($questions)->sortBy('average')->first();

        return [
            'section' => $lowest['section'],
            'label' => $lowest['label'],
            'average' => $lowest['average'],
        ];
    }

    /**
     * @return list<array{label: string, value: int}>
     */
    private function scales(): array
    {
        $stored = EvaluationScale::query()->orderBy('position')->get();

        if ($stored->isEmpty()) {
            return EvaluationScale::defaultScales();
        }

        return $stored->map(fn (EvaluationScale $scale): array => [
            'label' => $scale->label,
            'value' => (int) $scale->value,
        ])->all();
    }

    /**
     * Jawaban kuesioner baru, atau indikator lama bila peserta mengisi sebelum bank pernyataan dipakai.
     *
     * @return list<array{label: string, score: int|string}>
     */
    private function criteriaItems(Evaluation $evaluation): array
    {
        $stored = $evaluation->criteria;

        if (is_array($stored) && $stored !== []) {
            return $stored;
        }

        return array_values(array_filter(
            $evaluation->criteriaForDisplay(),
            fn (mixed $criterion): bool => is_array($criterion)
                && isset($criterion['score'])
                && (int) $criterion['score'] >= 1,
        ));
    }

    /**
     * @param  Collection<int, Evaluation>  $evaluations
     * @param  array<string, array{label: string, order: array<string, int>}>  $catalog
     * @return list<array{participant: string, unit_bisnis: string, departemen: string, mentor: string, scores: array{program: ?float, departemen: ?float, mentor: ?float}, average: float, comments: ?string, answers: list<array{section: string, label: string, score: float}>}>
     */
    private function rows(Collection $evaluations, array $catalog): array
    {
        return $evaluations->map(function (Evaluation $evaluation) use ($catalog): array {
            $scored = collect($this->scoredCriteria($evaluation, $catalog));
            $bySection = $scored
                ->groupBy('key')
                ->map(fn (Collection $group): ?float => $this->average($group->pluck('score')->all()));

            return [
                'participant' => $evaluation->program?->participant?->user?->name
                    ?? $evaluation->evaluator?->name
                    ?? 'Responden',
                'unit_bisnis' => $evaluation->program?->department?->name ?? '—',
                'departemen' => $evaluation->program?->businessUnit?->name ?? '—',
                'mentor' => $evaluation->program?->mentor?->user?->name ?? '—',
                'scores' => [
                    'program' => $bySection->get('program'),
                    'departemen' => $bySection->get('departemen'),
                    'mentor' => $bySection->get('mentor'),
                ],
                'average' => $evaluation->average(),
                'comments' => filled($evaluation->comments) ? $evaluation->comments : null,
                'answers' => $scored->map(fn (array $item): array => [
                    'section' => $item['section'],
                    'label' => $item['label'],
                    'score' => $item['score'],
                ])->values()->all(),
            ];
        })->all();
    }

    /**
     * @param  Collection<int, Evaluation>  $evaluations
     * @param  array<string, array{label: string, order: array<string, int>}>  $catalog
     * @param  callable(Evaluation): ?int  $key
     * @param  callable(Evaluation): string  $label
     * @return list<array{label: string, average: float}>
     */
    private function breakdown(Collection $evaluations, array $catalog, callable $key, callable $label): array
    {
        return $evaluations
            ->filter(fn (Evaluation $evaluation): bool => $key($evaluation) !== null)
            ->groupBy(fn (Evaluation $evaluation): int => (int) $key($evaluation))
            ->map(function (Collection $group) use ($catalog, $label): array {
                $scores = [];

                foreach ($group as $evaluation) {
                    foreach ($this->scoredCriteria($evaluation, $catalog) as $item) {
                        $scores[] = $item['score'];
                    }
                }

                return [
                    'label' => $label($group->first()),
                    'average' => $this->average($scores) ?? 0.0,
                ];
            })
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }
}
