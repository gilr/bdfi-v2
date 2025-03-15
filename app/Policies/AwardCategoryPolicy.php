<?php

namespace App\Policies;

use App\Models\AwardCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AwardCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, AwardCategory $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, AwardCategory $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, AwardCategory $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->creator;
        return $user->hasAdminRole();
    }
    public function restore(User $user, AwardCategory $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->destroyer;
        return $user->hasAdminRole();
    }
    public function forceDelete(User $user, AwardCategory $record)
    {
        return $user->hasSysAdminRole();
    }
}
