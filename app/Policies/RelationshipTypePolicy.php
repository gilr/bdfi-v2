<?php

namespace App\Policies;

use App\Models\RelationshipType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RelationshipTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, RelationshipType $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasAdminRole();
    }
    public function update(User $user, RelationshipType $record)
    {
        return $user->hasAdminRole();
    }
    public function delete(User $user, RelationshipType $record)
    {
        return $user->hasAdminRole();
    }
    public function restore(User $user, RelationshipType $record)
    {
        return $user->hasAdminRole() || $user->id === $record->destroyer;
    }
    public function forceDelete(User $user, RelationshipType $record)
    {
        return $user->hasSysAdminRole();
    }
}
