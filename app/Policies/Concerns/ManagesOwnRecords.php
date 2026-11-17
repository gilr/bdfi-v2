<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Règles communes de suppression et restauration des tables non internes.
 *
 *   Membre   : d = supprimer si on est le créateur et que personne d'autre n'a modifié depuis
 *              r = restaurer si on est celui qui a supprimé
 *   Admin    : D, R sans condition
 *   Sysadmin : idem admin (+ forceDelete, porté par chaque policy)
 */
trait ManagesOwnRecords
{
    protected function canDeleteRecord(User $user, Model $record): bool
    {
        return $user->hasAdminRole()
            || ($user->hasMemberRole() && $this->isUntouchedOwnRecord($user, $record));
    }

    protected function canRestoreRecord(User $user, Model $record): bool
    {
        return $user->hasAdminRole()
            || ($user->hasMemberRole() && (int) $record->deleted_by === $user->id);
    }

    /**
     * Créé par l'utilisateur, et aucune modification par quelqu'un d'autre depuis :
     * dernier modificateur (updated_by) et, si le modèle est historisé, historique des révisions.
     */
    private function isUntouchedOwnRecord(User $user, Model $record): bool
    {
        if ((int) $record->created_by !== $user->id) {
            return false;
        }
        if ($record->updated_by !== null && (int) $record->updated_by !== $user->id) {
            return false;
        }
        if (method_exists($record, 'revisionHistory')) {
            return ! $record->revisionHistory()
                ->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', '!=', $user->id))
                ->exists();
        }
        return true;
    }
}
