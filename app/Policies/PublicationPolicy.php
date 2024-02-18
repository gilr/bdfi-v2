<?php

namespace App\Policies;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Publication $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Publication $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Publication $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->creator;
        return $user->hasAdminRole();
    }
    public function restore(User $user, Publication $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->destructor;
        return $user->hasAdminRole();
    }
    public function forceDelete(User $user, Publication $record)
    {
        return $user->hasSysAdminRole();
    }
}
