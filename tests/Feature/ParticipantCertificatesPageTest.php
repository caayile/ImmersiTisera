<?php

namespace Tests\Feature;

use App\Models\EvaluationQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantCertificatesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_sees_certificate_on_evaluation_page_after_unlock(): void
    {
        $this->seed();

        $participant = User::where('email', 'dosen@imersi.id')->firstOrFail();
        $mentor = User::where('email', 'mentor@imersi.id')->firstOrFail();
        $program = $participant->participant->programs()->latest()->firstOrFail();
        $signature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $this->actingAs($mentor)
            ->post(route('mentor.certificates.signature'), ['certificate_signature' => $signature])
            ->assertRedirect();

        $this->actingAs($mentor)
            ->post(route('mentor.evaluations.store', $program), [
                'groups' => [
                    ['name' => 'Project', 'weight' => 100, 'aspects' => [['label' => 'Kualitas', 'score' => 90]]],
                ],
            ])
            ->assertRedirect();

        $this->actingAs($mentor)
            ->post(route('mentor.evaluations.approve', $program))
            ->assertRedirect();

        $this->actingAs($participant)
            ->post(route('participant.evaluation'), [
                'criteria' => collect(EvaluationQuestion::defaultBank())
                    ->flatMap(fn ($section) => $section['questions'])
                    ->map(fn ($question) => ['label' => $question, 'score' => 5])
                    ->all(),
            ])
            ->assertRedirect(route('participant.evaluation').'#hasil');

        $this->actingAs($participant)
            ->get(route('participant.certificates'))
            ->assertRedirect(route('participant.evaluation').'#sertifikat');

        $this->actingAs($participant)
            ->get(route('participant.evaluation'))
            ->assertOk()
            ->assertSee('Sertifikat penyelesaian')
            ->assertSee('Lihat / Cetak');
    }
}
