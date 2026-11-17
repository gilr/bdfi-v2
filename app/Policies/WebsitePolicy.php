<?php

namespace App\Policies;

use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use App\Models\Website;
use Illuminate\Auth\Access\HandlesAuthorization;

class WebsitePolicy
{
    use ManagesOwnRecords;
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Website $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Website $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Website $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Website $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Website $record)
    {
        return $user->hasSysAdminRole();
    }
}
