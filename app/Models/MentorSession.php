<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'program_id', 'mentor_id', 'participant_id', 'week', 'session_date', 'activity', 'findings',
    'current_work', 'next_action', 'feedback', 'checkpoint_status', 'evidence_link', 'attachment_path',
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

    public function mentor()
    {
        return $this->belongsTo(Mentor::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
