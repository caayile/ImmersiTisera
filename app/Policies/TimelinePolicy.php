<?php

namespace App\Policies;

use App\Models\Timeline;
use App\Models\User;

class TimelinePolicy
{
    public function view(User $user, Timeline $timeline): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $timeline->program?->participant?->user_id === $user->id
            || $timeline->program?->mentor?->user_id === $user->id;
    }

    public function update(User $user, Timeline $timeline): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $timeline->program?->mentor?->user_id === $user->id;
    }
}
