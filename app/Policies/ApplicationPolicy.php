<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $application->participant?->user_id === $user->id
            || $application->mentor?->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isParticipant();
    }

    public function review(User $user, Application $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $application->mentor?->user_id === $user->id;
    }
}