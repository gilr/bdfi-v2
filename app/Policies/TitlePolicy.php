<?php

namespace App\Policies;

use App\Models\Title;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class TitlePolicy
{
    use ManagesOwnRecords;
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Title $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Title $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Title $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Title $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Title $record)
    {
        return $user->hasSysAdminRole();
    }
}
