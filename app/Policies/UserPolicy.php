<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Droits sur la table des utilisateurs (Filament).
 *
 * Sans cette policy, Filament autorise TOUT à tout utilisateur du panneau
 * (dont le changement de rôle, y compris le sien).
 *   - Consultation : membre et plus (pas les invités/proposants : données personnelles)
 *   - Création / modification (dont les rôles) / suppression : admin et sysadmin
 *   - Suppression définitive : sysadmin
 */
class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasMemberRole();
    }
    public function view(User $user, User $record)
    {
        return $user->hasMemberRole();
    }
    public function create(User $user)
    {
        return $user->hasAdminRole();
    }
    public function update(User $user, User $record)
    {
        return $user->hasAdminRole();
    }
    public function delete(User $user, User $record)
    {
        return $user->hasAdminRole();
    }
    public function restore(User $user, User $record)
    {
        return $user->hasAdminRole();
    }
    public function forceDelete(User $user, User $record)
    {
        return $user->hasSysAdminRole();
    }
}
