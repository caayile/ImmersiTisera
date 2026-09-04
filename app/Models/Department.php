<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function isDirectPlacement(): bool
    {
        $units = $this->relationLoaded('businessUnits')
            ? $this->businessUnits
            : $this->businessUnits()->get();

        return $units->count() === 1 && $units->first()->name === $this->name;
    }

    public function primaryUnit(): ?BusinessUnit
    {
        $units = $this->relationLoaded('businessUnits')
            ? $this->businessUnits
            : $this->businessUnits();

        if ($units instanceof HasMany) {
            return $units->where('status', 'open')->first();
        }

        return $units->firstWhere('status', 'open') ?? $units->first();
    }
}
