<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Document $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Document $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Document $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->creator;
        return $user->hasAdminRole();
    }
    public function restore(User $user, Document $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->destructor;
        return $user->hasAdminRole();
    }
    public function forceDelete(User $user, Document $record)
    {
        return $user->hasSysAdminRole();
    }
}
