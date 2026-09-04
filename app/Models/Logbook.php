<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'program_id', 'participant_id', 'date', 'activity', 'what_i_did', 'what_i_learned',
    'what_i_found', 'value', 'next_action', 'attachment_path', 'mentor_feedback', 'status',
])]
class Logbook extends Model
{
    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }
}
