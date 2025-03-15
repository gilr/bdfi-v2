<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CollectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Collection $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Collection $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Collection $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->creator;
        return $user->hasAdminRole();
    }
    public function restore(User $user, Collection $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->destroyer;
        return $user->hasAdminRole();
    }
    public function forceDelete(User $user, Collection $record)
    {
        return $user->hasSysAdminRole();
    }

/*  Pas besoin, traité dans le StoreCollectionRequest
    public function store(User $user)
    {
        return true;
        //return $user->hasMemberRole();
    }
*/
}
