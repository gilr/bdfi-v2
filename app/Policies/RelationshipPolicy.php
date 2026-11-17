<?php

namespace App\Policies;

use App\Models\Relationship;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class RelationshipPolicy
{
    use ManagesOwnRecords;
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Relationship $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Relationship $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Relationship $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Relationship $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Relationship $record)
    {
        return $user->hasSysAdminRole();
    }
}
