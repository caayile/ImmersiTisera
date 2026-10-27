<?php

namespace App\Jobs;

use App\Models\Agreement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\CarbonInterface;

/**
 * @implements ShouldQueue
 */
class IssueAgreementLetterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Agreement $agreement;
    public ?CarbonInterface $date;

    public function __construct(Agreement $agreement, ?CarbonInterface $date = null)
    {
        $this->agreement = $agreement;
        $this->date = $date;
    }

    public function handle(): void
    {
        // Generate the letter number (which also updates the model)
        $letterNumber = Agreement::getNextGlobalSequence();
        Agreement::issue($this->agreement, $this->date);

        // In a real implementation, we would generate and send a PDF here
        // For now, we'll just log the action
        \Log::info('Generated agreement letter for ' . $this->agreement->id . ' with number: ' . $this->agreement->letter_number);
    }
}