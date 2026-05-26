<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Schedule $schedule): bool
    {
        return $this->manage($user) || $schedule->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $this->manage($user);
    }

    public function update(User $user, Schedule $schedule): bool
    {
        return $this->manage($user);
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $this->manage($user);
    }

    private function manage(User $user): bool
    {
        return $user->isManager() || $user->isAdmin();
    }
}
