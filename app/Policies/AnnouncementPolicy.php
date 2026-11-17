<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnnouncementPolicy
{
    use ManagesOwnRecords;
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Announcement $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasAdminRole();
    }
    public function update(User $user, Announcement $record)
    {
        return $user->hasAdminRole();
    }
    public function delete(User $user, Announcement $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Announcement $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Announcement $record)
    {
        return $user->hasSysAdminRole();
    }
}
