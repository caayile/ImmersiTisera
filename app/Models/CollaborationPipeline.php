<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'program_id', 'level', 'collaboration_type', 'description', 'next_action',
    'responsible_person', 'target_date', 'notes',
])]
class CollaborationPipeline extends Model
{
    protected function casts(): array
    {
        return ['target_date' => 'date'];
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
