<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'description', 'function', 'area', 'status'])]
class Department extends Model
{
    public function businessUnits()
    {
        return $this->hasMany(BusinessUnit::class);
    }

    public function mentors()
    {
        return $this->hasMany(Mentor::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }
}
