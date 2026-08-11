<?php

namespace App\Policies;

use App\Models\JobPosting;
use App\Models\User;

class JobPostingPolicy
{
    public function update(User $user, JobPosting $jobPosting): bool
    {
        return $user->isAdmin() || $jobPosting->user_id === $user->id;
    }

    public function delete(User $user, JobPosting $jobPosting): bool
    {
        return $user->isAdmin() || $jobPosting->user_id === $user->id;
    }
}
