<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'program_id', 'mentor_id', 'participant_id', 'week', 'session_date', 'findings',
    'current_work', 'next_action', 'feedback', 'checkpoint_status',
])]
class MentorSession extends Model
{
    protected function casts(): array
    {
        return ['session_date' => 'date'];
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
