<?php

namespace App\Policies;

use App\Models\Stat;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class StatPolicy
{
    use ManagesOwnRecords;
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
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Stat $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Stat $record)
    {
        return $user->hasSysAdminRole();
    }
}
