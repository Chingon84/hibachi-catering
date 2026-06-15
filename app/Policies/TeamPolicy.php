<?php

namespace App\Policies;

use App\Models\User;

class TeamPolicy
{
    /**
     * Can the current user view the team member list?
     */
    public function viewAny(User $currentUser): bool
    {
        return $currentUser->hasPermission('team.view');
    }

    /**
     * Can the current user view a specific team member?
     */
    public function view(User $currentUser, User $targetUser): bool
    {
        return $currentUser->hasPermission('team.view');
    }

    /**
     * Can the current user create a new team member?
     */
    public function create(User $currentUser): bool
    {
        return $currentUser->hasPermission('team.manage');
    }

    /**
     * Can the current user update a team member's profile?
     * Owners can only be updated by other owners.
     */
    public function update(User $currentUser, User $targetUser): bool
    {
        if ($targetUser->isOwner() && ! $currentUser->isOwner()) {
            return false;
        }

        return $currentUser->hasPermission('team.manage');
    }

    /**
     * Can the current user delete a team member?
     * Owners can only be deleted by other owners.
     */
    public function delete(User $currentUser, User $targetUser): bool
    {
        if ($targetUser->isOwner() && ! $currentUser->isOwner()) {
            return false;
        }

        return $currentUser->hasPermission('team.manage');
    }

    /**
     * Can the current user toggle admin access for a team member?
     * Owners cannot have their access toggled by non-owners.
     */
    public function toggleAccess(User $currentUser, User $targetUser): bool
    {
        if ($targetUser->isOwner() && ! $currentUser->isOwner()) {
            return false;
        }

        return $currentUser->hasPermission('team.manage');
    }
}
