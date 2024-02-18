<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum GenreAppartenance: string implements HasLabel {
    case OUI     = 'oui';
    case PARTIEL = 'partiel';
    case NON     = 'non';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OUI     => 'Oui',
            self::PARTIEL => 'Partiel',
            self::NON     => 'Non',
        };
    }
}
