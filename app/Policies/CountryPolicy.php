<?php

namespace App\Policies;

use App\Models\Country;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CountryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Country $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasAdminRole();
    }
    public function update(User $user, Country $record)
    {
        return $user->hasAdminRole();
    }
    public function delete(User $user, Country $record)
    {
        return $user->hasAdminRole();
    }
    public function restore(User $user, Country $record)
    {
        return $user->hasAdminRole() || $user->id === $record->destructor;
    }
    public function forceDelete(User $user, Country $record)
    {
        return $user->hasSysAdminRole();
    }
}
