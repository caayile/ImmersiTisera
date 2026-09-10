<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'department_id', 'business_unit_id', 'position', 'expertise', 'availability'])]
class Mentor extends Model
{
    protected function casts(): array
    {
        return ['expertise' => 'array'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function businessUnit()
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
