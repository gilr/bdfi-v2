<?php

namespace App\Policies;

use App\Models\Cycle;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class CyclePolicy
{
    use ManagesOwnRecords;
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Cycle $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Cycle $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Cycle $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Cycle $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Cycle $record)
    {
        return $user->hasSysAdminRole();
    }
}
