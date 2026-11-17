<?php

namespace App\Policies;

use App\Models\AuthorPublication;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\Response;

class AuthorPublicationPolicy
{
    use ManagesOwnRecords;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, AuthorPublication $record): bool
    {
        return $user->hasGuestRole();
    }
    public function create(User $user): bool
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, AuthorPublication $record): bool
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, AuthorPublication $record): bool
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, AuthorPublication $record): bool
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, AuthorPublication $record): bool
    {
        return $user->hasSysAdminRole();
    }
}
