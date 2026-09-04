<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'nidn', 'prodi', 'department', 'expertise', 'interests',
    'experience', 'purpose', 'goals', 'competency_gap',
])]
class DosenProfile extends Model
{
    protected function casts(): array
    {
        return [
            'expertise' => 'array',
            'interests' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
