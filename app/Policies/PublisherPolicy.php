<?php

namespace App\Policies;

use App\Models\Publisher;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublisherPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Publisher $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Publisher $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Publisher $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->creator;
        return $user->hasAdminRole();
    }
    public function restore(User $user, Publisher $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->destructor;
        return $user->hasAdminRole();
    }
    public function forceDelete(User $user, Publisher $record)
    {
        return $user->hasSysAdminRole();
    }
}
