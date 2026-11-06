<?php

namespace Database\Seeders;

use App\Models\Evaluation;
use App\Models\EvaluationQuestion;
use App\Models\Program;
use Illuminate\Database\Seeder;

class DummyQuestionnaireDataSeeder extends Seeder
{
    public function run(): void
    {
        $programs = Program::where('status', 'active')->limit(3)->get();
        
        if ($programs->isEmpty()) {
            $this->command->info('No active programs found.');
            return;
        }

        $bank = EvaluationQuestion::defaultBank();
        $questions = [];
        foreach ($bank as $section) {
            foreach ($section['questions'] as $q) {
                $questions[] = $q;
            }
        }

        foreach ($programs as $p) {
            $criteria = [];
            foreach ($questions as $q) {
                $criteria[] = ['label' => $q, 'score' => rand(4, 5)];
            }

            Evaluation::updateOrCreate(
                ['program_id' => $p->id],
                [
                    'evaluator_id' => $p->participant?->user_id ?? 1,
                    'criteria' => $criteria,
                    'comments' => 'Program sangat bermanfaat dan mentor sangat membantu.',
                ]
            );
        }

        $this->command->info("Dummy data created for {$programs->count()} programs.");
    }
}
