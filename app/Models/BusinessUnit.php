<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'department_id', 'name', 'description', 'function', 'image_path', 'work_done', 'example_activities',
    'requirements', 'relevant_programs', 'period', 'batch', 'registration_deadline', 'registration_start', 'status',
])]
class BusinessUnit extends Model
{
    public const MAX_APPLICANTS = 2;

    protected function casts(): array
    {
        return [
            'relevant_programs' => 'array',
            'registration_deadline' => 'datetime',
            'registration_start' => 'datetime',
        ];
    }

    public function isOpen(): bool
    {
        if (! $this->registration_deadline) {
            return false;
        }

        if ($this->status !== 'open') {
            return false;
        }

        if ($this->registration_start && now()->lt($this->registration_start)) {
            return false;
        }

        if (now()->gt($this->registration_deadline)) {
            return false;
        }

        return true;
    }

    public function isScheduled(): bool
    {
        return (bool) $this->registration_deadline;
    }

    public function isClosed(): bool
    {
        return ! $this->isOpen();
    }

    /**
     * Preload the batch-aware quota count to avoid N+1 queries, e.g.
     * `BusinessUnit::query()->withQuotaCount()->get()`.
     *
     * @param  Builder<BusinessUnit>  $query
     */
    public function scopeWithQuotaCount($query)
    {
        return $query->withCount(['applications as active_applications_count' => function ($count) {
            $count->where('status', '!=', 'rejected')
                ->where(function ($count) {
                    $count->whereColumn('applications.batch', 'business_units.batch')
                        ->orWhere(function ($count) {
                            $count->whereNull('applications.batch')->whereNull('business_units.batch');
                        });
                });
        }]);
    }

    public function activeApplicantsCount(): int
    {
        if ($this->hasAttribute('active_applications_count') && $this->active_applications_count !== null) {
            return (int) $this->active_applications_count;
        }

        return $this->applications()->forQuota($this)->count();
    }

    public function remainingSlots(): int
    {
        return max(0, self::MAX_APPLICANTS - $this->activeApplicantsCount());
    }

    public function isFull(): bool
    {
        return $this->activeApplicantsCount() >= self::MAX_APPLICANTS;
    }

    public function imageUrl(): ?string
    {
        return HeroSetting::resolveMediaUrl($this->image_path);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function mentors()
    {
        return $this->hasMany(Mentor::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
