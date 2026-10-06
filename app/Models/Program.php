<?php

namespace App\Models;

use App\Support\Status;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'application_id', 'participant_id', 'mentor_id', 'department_id', 'business_unit_id',
    'start_date', 'end_date', 'progress', 'current_week', 'status',
])]
class Program extends Model
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function mentor()
    {
        return $this->belongsTo(Mentor::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function businessUnit()
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function agreement()
    {
        return $this->hasOne(Agreement::class);
    }

    public function timelines()
    {
        return $this->hasMany(Timeline::class)->orderBy('week');
    }

    public function logbooks()
    {
        return $this->hasMany(Logbook::class)->orderByDesc('date');
    }

    public function mentorSessions()
    {
        return $this->hasMany(MentorSession::class)->orderBy('week');
    }

    public function outputs()
    {
        return $this->hasMany(ProgramOutput::class);
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function collaboration()
    {
        return $this->hasOne(CollaborationPipeline::class);
    }

    public function refreshProgress(): void
    {
        $week = $this->computedWeek();
        $logPct = min(100, $this->logbooks()->count() * 8);
        $this->update([
            'current_week' => $this->status === 'active' ? $week : $this->current_week,
            'progress' => $this->status === 'completed' ? 100 : min(95, $logPct),
        ]);
    }

    /**
     * Minggu program berjalan berdasarkan tanggal mulai (1–8).
     */
    public function computedWeek(): int
    {
        if ($this->status !== 'active' || ! $this->start_date) {
            return min(8, max(1, (int) ($this->current_week ?: 1)));
        }

        return min(8, max(1, (int) ceil(($this->start_date->diffInDays(now()) + 1) / 7)));
    }

    /**
     * Checkpoint minggu W terbuka dari minggu W sampai sebelum checkpoint berikutnya.
     * Contoh: minggu 2 terbuka di minggu 2–3; tertutup saat masuk minggu 4.
     */
    public function isCheckpointOpen(int $week): bool
    {
        if ($this->status !== 'active' || ! in_array($week, Status::CHECKPOINT_WEEKS, true)) {
            return false;
        }

        $current = $this->computedWeek();

        if ($current < $week) {
            return false;
        }

        $next = null;
        foreach (Status::CHECKPOINT_WEEKS as $candidate) {
            if ($candidate > $week) {
                $next = $candidate;
                break;
            }
        }

        return $next === null || $current < $next;
    }

    public function canComplete(): bool
    {
        return $this->outputs()->where('is_main_output', true)->where('status', 'approved')->exists()
            && $this->outputs()->where('is_final_report', true)->where('status', 'approved')->exists()
            && $this->evaluations()->count() >= 1;
    }

    public function seedTimeline(): void
    {
        if ($this->timelines()->exists()) {
            return;
        }

        foreach (Status::TIMELINE as $week => $meta) {
            if (! in_array($week, Status::CHECKPOINT_WEEKS, true)) {
                continue;
            }

            $this->timelines()->create([
                'week' => $week,
                'phase' => $meta['phase'],
                'title' => $meta['title'],
                'description' => $meta['description'],
                'expected_output' => $meta['output'],
                'status' => 'pending',
            ]);
        }
    }
}
