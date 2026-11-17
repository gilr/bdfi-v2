<?php

namespace App\Policies;

use App\Models\Publisher;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublisherPolicy
{
    use ManagesOwnRecords;
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Publisher $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Publisher $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Publisher $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Publisher $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Publisher $record)
    {
        return $user->hasSysAdminRole();
    }
}
