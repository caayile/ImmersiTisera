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
        $week = min(8, max(1, (int) ceil((($this->start_date?->diffInDays(now()) ?? 0) + 1) / 7)));
        $logPct = min(100, $this->logbooks()->count() * 8);
        $this->update([
            'current_week' => $this->status === 'active' ? $week : $this->current_week,
            'progress' => $this->status === 'completed' ? 100 : min(95, $logPct),
        ]);
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
