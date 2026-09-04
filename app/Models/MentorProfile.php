<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'user_id', 'company_name', 'industry_field', 'business_unit', 'department_function',
    'job_title', 'expertise', 'industry_needs', 'problems', 'opportunities',
    'dosen_needs', 'availability',
])]
class MentorProfile extends Model
{
    protected function casts(): array
    {
        return [
            'expertise' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
