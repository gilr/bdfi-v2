<?php

namespace App\Policies;

use App\Models\Relationship;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RelationshipPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Relationship $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Relationship $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Relationship $record)
    {
        return $user->hasMemberRole();
    }
    public function restore(User $user, Relationship $record)
    {
        return $user->hasAdminRole() || $user->id === $record->destroyer;
    }
    public function forceDelete(User $user, Relationship $record)
    {
        return $user->hasSysAdminRole();
    }
}
