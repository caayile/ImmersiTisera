<?php

namespace App\Policies;

use App\Models\ProgramOutput;
use App\Models\User;

class ProgramOutputPolicy
{
    public function view(User $user, ProgramOutput $output): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $output->participant?->user_id === $user->id
            || $output->program?->mentor?->user_id === $user->id;
    }

    public function create(User $user, ProgramOutput $output): bool
    {
        return $output->participant?->user_id === $user->id;
    }

    public function review(User $user, ProgramOutput $output): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $output->program?->mentor?->user_id === $user->id;
    }
}
