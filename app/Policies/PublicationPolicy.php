<?php

namespace App\Policies;

use App\Models\Publication;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicationPolicy
{
    use ManagesOwnRecords;
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Publication $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Publication $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Publication $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Publication $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Publication $record)
    {
        return $user->hasSysAdminRole();
    }
}
