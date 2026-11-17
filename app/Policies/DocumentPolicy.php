<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    use ManagesOwnRecords;

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
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Document $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Document $record)
    {
        return $user->hasSysAdminRole();
    }
}
