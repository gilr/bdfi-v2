<?php

namespace App\Policies;

use App\Models\Translator;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TranslatorPolicy
{
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
        return $user->hasMemberRole() && $user->id === $record->creator;
    }
    public function restore(User $user, Translator $record)
    {
        return $user->hasAdminRole() || $user->id === $record->destroyer;
    }
    public function forceDelete(User $user, Translator $record)
    {
        return $user->hasSysAdminRole();
    }
}
