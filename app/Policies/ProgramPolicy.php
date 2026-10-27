<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    public function view(User $user, Program $program): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $program->participant?->user_id === $user->id
            || $program->mentor?->user_id === $user->id;
    }

    public function update(User $user, Program $program): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $program->participant?->user_id === $user->id
            || $program->mentor?->user_id === $user->id;
    }

    public function complete(User $user, Program $program): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $program->mentor?->user_id === $user->id;
    }
}