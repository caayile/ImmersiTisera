<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'participant_id', 'department_id', 'business_unit_id', 'mentor_id', 'motivation',
    'preferred_period', 'match_score', 'relevance_warning', 'matching_notes', 'status',
])]
class Application extends Model
{
    protected function casts(): array
    {
        return ['relevance_warning' => 'boolean'];
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function businessUnit()
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function mentor()
    {
        return $this->belongsTo(Mentor::class);
    }

    public function program()
    {
        return $this->hasOne(Program::class);
    }
}
