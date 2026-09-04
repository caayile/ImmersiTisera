<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['created_by', 'prodi', 'department', 'purpose', 'academic_needs', 'problem', 'goal'])]
class DepartmentNeed extends Model
{
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
