<?php

namespace App\Policies;

use App\Models\Signature;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class SignaturePolicy
{
    use ManagesOwnRecords;
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Signature $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Signature $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Signature $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Signature $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Signature $record)
    {
        return $user->hasSysAdminRole();
    }
}
