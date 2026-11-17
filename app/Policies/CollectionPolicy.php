<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class CollectionPolicy
{
    use ManagesOwnRecords;
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Collection $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Collection $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Collection $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Collection $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Collection $record)
    {
        return $user->hasSysAdminRole();
    }

/*  Pas besoin, traité dans le StoreCollectionRequest
    public function store(User $user)
    {
        return true;
        //return $user->hasMemberRole();
    }
*/
}
