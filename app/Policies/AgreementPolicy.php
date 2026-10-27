<?php

namespace App\Policies;

use App\Models\Agreement;
use App\Models\User;

class AgreementPolicy
{
    public function view(User $user, Agreement $agreement): bool
    {
        $program = $agreement->program;

        if (! $program) {
            return false;
        }

        return $user->isAdmin()
            || $program->participant?->user_id === $user->id
            || $program->mentor?->user_id === $user->id;
    }

    public function update(User $user, Agreement $agreement): bool
    {
        $program = $agreement->program;

        if (! $program) {
            return false;
        }

        return $program->participant?->user_id === $user->id;
    }

    public function approve(User $user, Agreement $agreement): bool
    {
        $program = $agreement->program;

        if (! $program) {
            return false;
        }

        return $program->participant?->user_id === $user->id
            || $program->mentor?->user_id === $user->id;
    }
}