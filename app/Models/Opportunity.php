<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'mentor_id', 'title', 'field', 'business_unit', 'purpose', 'problem',
    'opportunity', 'needed_expertise', 'expected_output', 'allowed_activities',
    'timeline_start', 'timeline_end', 'status',
])]
class Opportunity extends Model
{
    protected function casts(): array
    {
        return [
            'needed_expertise' => 'array',
            'allowed_activities' => 'array',
            'timeline_start' => 'date',
            'timeline_end' => 'date',
        ];
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
