<?php

namespace App\Services;

use App\Models\BusinessUnit;
use App\Models\Participant;

class MatchingService
{
    public function score(Participant $participant, BusinessUnit $unit): array
    {
        $skills = collect($participant->expertise ?? [])
            ->merge($participant->competency ?? [])
            ->map(fn ($item) => strtolower(trim((string) $item)))
            ->filter();

        $needed = collect($unit->relevant_programs ?? [])
            ->merge([$unit->function, $unit->name, $unit->description])
            ->flatMap(fn ($item) => preg_split('/[,\/;]+/', strtolower((string) $item)) ?: [])
            ->map(fn ($item) => trim($item))
            ->filter(fn ($item) => strlen($item) > 2);

        $hits = $skills->filter(fn ($skill) => $needed->contains(fn ($need) => str_contains($need, $skill) || str_contains($skill, $need)))->count();
        $score = min(99, 40 + ($hits * 12) + ($participant->study_program ? 10 : 0));

        $programs = collect($unit->relevant_programs ?? [])->map(fn ($item) => strtolower((string) $item));
        $warning = $programs->isNotEmpty() && $participant->study_program
            && ! $programs->contains(fn ($item) => str_contains($item, strtolower($participant->study_program)) || str_contains(strtolower($participant->study_program), $item));

        if ($warning) {
            $score = max(30, $score - 20);
        }

        return ['score' => $score, 'warning' => $warning];
    }
}
