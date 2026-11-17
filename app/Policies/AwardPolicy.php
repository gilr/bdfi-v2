<?php

namespace App\Policies;

use App\Models\Award;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class AwardPolicy
{
    use ManagesOwnRecords;
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
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Award $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Award $record)
    {
        return $user->hasSysAdminRole();
    }
}
