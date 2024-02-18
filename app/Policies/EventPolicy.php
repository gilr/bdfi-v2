<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
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
        // TBD        return $user->hasAdminRole() || $user->id === $author->creator;
        return $user->hasAdminRole();
    }
    public function restore(User $user, Event $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $author->destructor;
        return $user->hasAdminRole();
    }
    public function forceDelete(User $user, Event $record)
    {
        return $user->hasSysAdminRole();
    }
}
