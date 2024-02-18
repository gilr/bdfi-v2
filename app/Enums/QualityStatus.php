<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum QualityStatus: string implements HasLabel {
    case VIDE       = 'vide';
    case EBAUCHE    = 'ebauche';
    case MOYEN      = 'moyen';
    case ACCEPTABLE = 'acceptable';
    case TERMINE    = 'termine';
    case A_REVOIR   = 'a_revoir';
    case VALIDE     = 'valide';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::VIDE       => 'Vide',
            self::EBAUCHE    => 'Ebauche',
            self::MOYEN      => 'Moyen',
            self::ACCEPTABLE => 'Acceptable',
            self::TERMINE    => 'Terminé',
            self::A_REVOIR   => 'A réviser',
            self::VALIDE     => 'Validé',
        };
    }
}
