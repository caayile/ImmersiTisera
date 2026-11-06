<?php

namespace App\Jobs;

use App\Models\Agreement;
use App\Notifications\ImersiAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * @implements ShouldQueue
 */
class SendAgreementApprovedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Agreement $agreement;

    public function __construct(Agreement $agreement)
    {
        $this->agreement = $agreement;
    }

    public function handle(): void
    {
        $program = $this->agreement->program;
        if (! $program) {
            return;
        }

        $participant = $program->participant;
        $mentor = $program->mentor;

        if ($participant && $participant->user) {
            ImersiAlert::notify(
                $participant->user,
                'Perjanjian Magang Disetujui',
                'Perjanjian magang dengan '.($program->businessUnit?->name ?? 'unit bisnis').' telah disetujui. Silakan mulai program.',
                route('participant.agreement')
            );
        }

        if ($mentor && $mentor->user) {
            ImersiAlert::notify(
                $mentor->user,
                'Perjanjian Magang Disetujui',
                'Perjanjian magang dengan '.($program->participant?->user?->name ?? 'dosen').' telah disetujui. Silakan mulai program.',
                route('mentor.agreements')
            );
        }
    }
}
