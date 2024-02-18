<?php

namespace App\Policies;

use App\Models\Award;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AwardPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Award $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Award $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Award $record)
    {
        return $user->hasAdminRole();
    }
    public function restore(User $user, Award $record)
    {
        return $user->hasAdminRole();
    }
    public function forceDelete(User $user, Award $record)
    {
        return $user->hasSysAdminRole();
    }
}
