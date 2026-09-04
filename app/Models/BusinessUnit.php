<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'department_id', 'name', 'description', 'function', 'work_done', 'example_activities',
    'requirements', 'relevant_programs', 'period', 'status',
])]
class BusinessUnit extends Model
{
    protected function casts(): array
    {
        return ['relevant_programs' => 'array'];
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
