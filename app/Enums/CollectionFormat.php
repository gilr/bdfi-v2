<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum CollectionFormat: string implements HasLabel {
    case POCHE = 'poche';
    case MF    = 'mf';
    case GF    = 'gf';
    case MIXTE = 'mixte';
    case AUTRE = 'autre';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::POCHE => 'Poche',
            self::MF    => 'Moyen format',
            self::GF    => 'Grand format',
            self::MIXTE => 'Mixte',
            self::AUTRE => 'Autre',
        };
    }
}
