<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebsiteType;
use Illuminate\Auth\Access\HandlesAuthorization;

class WebsiteTypePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, WebsiteType $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasAdminRole();
    }
    public function update(User $user, WebsiteType $record)
    {
        return $user->hasAdminRole();
    }
    public function delete(User $user, WebsiteType $record)
    {
        return $user->hasAdminRole();
    }
    public function restore(User $user, WebsiteType $record)
    {
        return $user->hasAdminRole() || $user->id === $record->destroyer;
    }
    public function forceDelete(User $user, WebsiteType $record)
    {
        return $user->hasSysAdminRole();
    }
}
