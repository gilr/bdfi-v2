<?php

namespace App\Policies;

use App\Models\Reprint;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReprintPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Reprint $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Reprint $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Reprint $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->creator;
        return $user->hasAdminRole();
    }
    public function restore(User $user, Reprint $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->destructor;
        return $user->hasAdminRole();
    }
    public function forceDelete(User $user, Reprint $record)
    {
        return $user->hasSysAdminRole();
    }
}
