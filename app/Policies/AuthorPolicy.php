<?php

namespace App\Policies;

use App\Models\Author;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AuthorPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Author $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Author $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Author $record)
    {
        return ($user->hasMemberRole() && $user->id === $record->creator) || $user->hasSysAdminRole();
    }
    public function restore(User $user, Author $record)
    {
        return $user->hasAdminRole() || $user->id === $record->destroyer;
    }
    public function forceDelete(User $user, Author $record)
    {
        return $user->hasSysAdminRole();
    }

    // TBD si encore utile avec Filament ?!
    public function addWebsite(User $user, Author $record)
    {
        return $user->hasMemberRole();
    }
    public function attachAnyAuthor(User $user, Author $record)
    {
        return $user->hasMemberRole();
    }
    public function attachAuthor(User $user, Author $author, Author $author2)
    {
        return $user->hasMemberRole() && ! $author->signatures->contains($author2) && ! $author->references->contains($author2);
        //return false;
    }
    public function detachAuthor(User $user, Author $record, Author $author2)
    {
        return $user->hasMemberRole();
    }
}
