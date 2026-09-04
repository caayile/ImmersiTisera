<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['program_id', 'week', 'phase', 'title', 'description', 'expected_output', 'status'])]
class Timeline extends Model
{
    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
