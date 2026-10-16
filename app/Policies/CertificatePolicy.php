<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\Program;
use App\Models\User;

class CertificatePolicy
{
    public function view(User $user, Certificate $certificate): bool
    {
        $participant = $user->participant;
        $program = $certificate->program;

        if (
            ! $participant
            || ! $program
            || ! $certificate->isIssued()
            || (int) $program->participant_id !== (int) $participant->id
        ) {
            return false;
        }

        $program->loadMissing('evaluations');
        $hasMentorReport = $program->evaluations->contains(
            fn ($evaluation): bool => (int) $evaluation->evaluator_id !== (int) $user->id
                && $evaluation->hasReport()
        );

        if (! $hasMentorReport) {
            return false;
        }

        $currentProgramId = Program::query()
            ->where('participant_id', $participant->id)
            ->latest()
            ->value('id');

        if ((int) $program->id !== (int) $currentProgramId) {
            return true;
        }

        return $program->evaluations->contains(
            fn ($evaluation): bool => (int) $evaluation->evaluator_id === (int) $user->id
        );
    }
}
