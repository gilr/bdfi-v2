<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum GenreStat: string implements HasLabel {
    case SF          = 'sf';
    case FANTASTIQUE = 'fantastique';
    case FANTASY     = 'fantasy';
    case HYBRIDE     = 'hybride';
    case AUTRE       = 'autre';
    case MAINSTREAM  = 'mainstream';
    case INCONNU     = 'inconnu';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SF          => 'Science-fiction',
            self::FANTASTIQUE => 'Fantastique',
            self::FANTASY     => 'Fantasy',
            self::HYBRIDE     => 'Hybride',
            self::AUTRE       => 'Autre',
            self::MAINSTREAM  => 'Blanche / Mainstream',
            self::INCONNU     => 'Inconnu',
        };
    }
}
