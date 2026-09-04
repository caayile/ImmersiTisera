<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'nidn', 'study_program', 'expertise', 'competency', 'experience', 'motivation', 'profile_data'])]
class Participant extends Model
{
    protected function casts(): array
    {
        return [
            'expertise' => 'array',
            'competency' => 'array',
            'profile_data' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }
}
