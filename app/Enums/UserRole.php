<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel {
    case USER      = 'user';
    case PROPONENT = 'proponent';
    case GUEST     = 'guest';
    case MEMBER    = 'member';
    case ADMIN     = 'admin';
    case SYSADMIN  = 'sysadmin';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::USER       => 'Utilisateur',
            self::PROPONENT  => 'Contributeur',
            self::GUEST      => 'Invité',
            self::MEMBER     => 'Membre',         // ou "Bibliographe"
            self::ADMIN      => 'Administrateur',
            self::SYSADMIN   => 'Administrateur système',
        };
    }

    public function getDescription(): ?string
    {
        return match ($this) {
            self::USER       => 'Inscrit',
            self::PROPONENT  => 'Membre contributeur',
            self::GUEST      => 'Membre invité en mode découverte',
            self::MEMBER     => 'Membre bibliographe',
            self::ADMIN      => 'Administrateur bibliographe',
            self::SYSADMIN   => 'Administrateur système',
        };
    }
}
