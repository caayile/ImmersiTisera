<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'title', 'position'])]
class EvaluationSection extends Model
{
    public function questions()
    {
        return $this->hasMany(EvaluationQuestion::class)->orderBy('position');
    }
}
