<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'program_id', 'objective', 'problem_statement', 'activities', 'main_output',
    'participant_benefit', 'business_benefit', 'success_indicators', 'collaboration_potential',
    'revision_note', 'status', 'participant_approved_at', 'mentor_approved_at',
])]
class Agreement extends Model
{
    protected function casts(): array
    {
        return [
            'success_indicators' => 'array',
            'participant_approved_at' => 'datetime',
            'mentor_approved_at' => 'datetime',
        ];
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
