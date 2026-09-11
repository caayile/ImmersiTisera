<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'department_id', 'name', 'description', 'function', 'image_path', 'work_done', 'example_activities',
    'requirements', 'relevant_programs', 'period', 'registration_deadline', 'registration_start', 'status',
])]
class BusinessUnit extends Model
{
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
}
