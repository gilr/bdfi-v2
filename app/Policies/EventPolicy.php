<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use ManagesOwnRecords;
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Event $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Event $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Event $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Event $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Event $record)
    {
        return $user->hasSysAdminRole();
    }
}
