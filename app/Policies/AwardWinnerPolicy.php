<?php

namespace App\Policies;

use App\Models\AwardWinner;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AwardWinnerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, AwardWinner $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, AwardWinner $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, AwardWinner $record)
    {
        return $user->hasAdminRole();
    }
    public function restore(User $user, AwardWinner $record)
    {
        return $user->hasAdminRole();
    }
    public function forceDelete(User $user, AwardWinner $record)
    {
        return $user->hasSysAdminRole();
    }
}
