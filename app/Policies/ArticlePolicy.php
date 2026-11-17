<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use App\Policies\Concerns\ManagesOwnRecords;
use Illuminate\Auth\Access\Response;

class ArticlePolicy
{
    use ManagesOwnRecords;

    public function viewAny(User $user)
    {
        return $user->hasGuestRole();
    }
    public function view(User $user, Article $record)
    {
        return $user->hasGuestRole();
    }
    public function create(User $user)
    {
        return $user->hasMemberRole();
    }
    public function update(User $user, Article $record)
    {
        return $user->hasMemberRole();
    }
    public function delete(User $user, Article $record)
    {
        return $this->canDeleteRecord($user, $record);
    }
    public function restore(User $user, Article $record)
    {
        return $this->canRestoreRecord($user, $record);
    }
    public function forceDelete(User $user, Article $record)
    {
        return $user->hasSysAdminRole();
    }

}
