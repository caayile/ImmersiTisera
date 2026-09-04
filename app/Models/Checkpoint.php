<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['program_id', 'week', 'dosen_progress', 'mentor_status', 'mentor_notes'])]
class Checkpoint extends Model
{
    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
