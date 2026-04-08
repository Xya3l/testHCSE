<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;

class ProfilePolicy
{
    /**
     * Determine if the user can create profiles.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the profile.
     */
    public function update(User $user, Profile $profile): bool
    {
        return true;
    }

    /**
     * Determine if the user can delete the profile.
     */
    public function delete(User $user, Profile $profile): bool
    {
        return true;
    }
}
