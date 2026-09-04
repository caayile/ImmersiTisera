<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['program_id', 'content', 'status'])]
class Report extends Model
{
    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
