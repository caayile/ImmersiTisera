<?php

namespace App\Policies;

use App\Models\Logbook;
use App\Models\User;

class LogbookPolicy
{
    public function view(User $user, Logbook $logbook): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $logbook->participant?->user_id === $user->id
            || $logbook->program?->mentor?->user_id === $user->id;
    }

    public function create(User $user, Logbook $logbook): bool
    {
        return $logbook->participant?->user_id === $user->id;
    }

    public function review(User $user, Logbook $logbook): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $logbook->program?->mentor?->user_id === $user->id;
    }
}