<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum PublicationFormat: string implements HasLabel {
    case POCHE   = 'poche';
    case MF      = 'mf';
    case GF      = 'gf';
    case AUTRE   = 'autre';
    case NA      = 'n-a';
    case INCONNU = 'inconnu';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::POCHE   => 'Poche',
            self::MF      => 'Moyen format',
            self::GF      => 'Grand format',
            self::AUTRE   => 'Autre',
            self::NA      => 'Non applicable',
            self::INCONNU => 'Inconnu',
        };
    }
}
