<?php

namespace App\Policies;

use App\Models\AwardWinner;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\HandlesAuthorization;

class AwardWinnerPolicy
{
    use ManagesOwnRecords;
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, AwardWinner $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, AwardWinner $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, AwardWinner $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, AwardWinner $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, AwardWinner $record)
    {
        return $user->hasSysAdminRole();
    }
}
