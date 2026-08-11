<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function update(User $user, Application $application): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $application->jobPosting->user_id === $user->id;
    }
}
