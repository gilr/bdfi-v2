<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ArticlePolicy
{

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
        // TBD        return $user->hasAdminRole() || $user->id === $record->creator;
        return $user->hasAdminRole();
    }
    public function restore(User $user, Article $record)
    {
        // TBD        return $user->hasAdminRole() || $user->id === $record->destroyer;
        return $user->hasAdminRole();
    }
    public function forceDelete(User $user, Article $record)
    {
        return $user->hasSysAdminRole();
    }

}
