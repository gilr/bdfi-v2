<?php

namespace App\Policies;

use App\Models\TableOfContent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TableOfContentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasGuestRole();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TableOfContent $tableOfContent): bool
    {
        return $user->hasGuestRole();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasMemberRole();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TableOfContent $tableOfContent): bool
    {
        return $user->hasMemberRole();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TableOfContent $tableOfContent): bool
    {
        return $user->hasAdminRole();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TableOfContent $tableOfContent): bool
    {
        return $user->hasAdminRole();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TableOfContent $tableOfContent): bool
    {
        return $user->hasAdminRole();
    }
}
