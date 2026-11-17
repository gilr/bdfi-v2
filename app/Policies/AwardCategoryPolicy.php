<?php

namespace App\Policies;

use App\Models\AwardCategory;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class AwardCategoryPolicy
{
    use ManagesOwnRecords;
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, AwardCategory $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, AwardCategory $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, AwardCategory $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, AwardCategory $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, AwardCategory $record)
    {
        return $user->hasSysAdminRole();
    }
}
