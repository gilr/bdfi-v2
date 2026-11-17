<?php

namespace App\Policies;

use App\Models\Translator;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\Response;

class TranslatorPolicy
{
    use ManagesOwnRecords;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Translator $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Translator $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Translator $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Translator $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Translator $record)
    {
        return $user->hasSysAdminRole();
    }
}
