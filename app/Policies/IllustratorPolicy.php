<?php

namespace App\Policies;

use App\Models\Illustrator;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\Response;

class IllustratorPolicy
{
    use ManagesOwnRecords;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Illustrator $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Illustrator $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Illustrator $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Illustrator $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Illustrator $record)
    {
        return $user->hasSysAdminRole();
    }
}
