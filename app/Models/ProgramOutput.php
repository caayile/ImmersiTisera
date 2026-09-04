<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'program_id', 'participant_id', 'title', 'type', 'description', 'file_path',
    'is_main_output', 'is_final_report', 'mentor_feedback', 'status',
])]
class ProgramOutput extends Model
{
    protected $table = 'outputs';

    protected function casts(): array
    {
        return [
            'is_main_output' => 'boolean',
            'is_final_report' => 'boolean',
        ];
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
