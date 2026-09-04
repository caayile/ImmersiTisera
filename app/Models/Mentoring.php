<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['program_id', 'week', 'session_date', 'found', 'working_on', 'next_action', 'mentor_feedback'])]
class Mentoring extends Model
{
    protected function casts(): array
    {
        return [
            'session_date' => 'date',
        ];
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
