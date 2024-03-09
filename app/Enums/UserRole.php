<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasLabel {
    case USER      = 'user';
    case GUEST     = 'guest';
    case PROPONENT = 'proponent';
    case MEMBER    = 'member';
    case ADMIN     = 'admin';
    case SYSADMIN  = 'sysadmin';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::USER       => 'Utilisateur',
            self::GUEST      => 'Invité',
            self::PROPONENT  => 'Proposant',
            self::MEMBER     => 'Membre',
            self::ADMIN      => 'Administrateur',
            self::SYSADMIN   => 'Administrateur système',
        };
    }
}
