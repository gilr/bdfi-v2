<?php

namespace App\Policies;

use App\Models\Stat;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Stat $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasAdminRole();
    }
    public function update(User $user, Stat $record)
    {
        return $user->hasAdminRole();
    }
    public function delete(User $user, Stat $record)
    {
        return $user->hasAdminRole();
    }
    public function restore(User $user, Stat $record)
    {
        return $user->hasAdminRole() || $user->id === $record->destroyer;
    }
    public function forceDelete(User $user, Stat $record)
    {
        return $user->hasSysAdminRole();
    }
}
