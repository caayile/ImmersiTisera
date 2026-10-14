<?php

namespace Database\Seeders;

use App\Models\EvaluationQuestion;
use App\Models\EvaluationScale;
use App\Models\EvaluationSection;
use Illuminate\Database\Seeder;

class QuestionnaireBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        static::ensure();
    }

    /**
     * Sinkronkan bank bawaan (idempoten, aman dipanggil berulang).
     */
    public static function ensure(): void
    {
        foreach (EvaluationScale::defaultScales() as $position => $scale) {
            EvaluationScale::updateOrCreate(
                ['value' => $scale['value']],
                ['label' => $scale['label'], 'position' => $position + 1]
            );
        }

        foreach (EvaluationQuestion::defaultBank() as $position => $section) {
            $record = EvaluationSection::updateOrCreate(
                ['position' => $position + 1],
                ['key' => $section['key'], 'title' => $section['title']]
            );

            $keepIds = [];

            foreach ($section['questions'] as $index => $question) {
                $item = EvaluationQuestion::updateOrCreate(
                    [
                        'evaluation_section_id' => $record->id,
                        'position' => $index + 1,
                    ],
                    ['question' => $question, 'is_active' => true]
                );
                $keepIds[] = $item->id;
            }

            $record->questions()->whereNotIn('id', $keepIds)->delete();
        }
    }
}
