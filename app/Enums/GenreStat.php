<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum GenreStat: string implements HasLabel {
    case INCONNU     = 'inconnu';
    case SF          = 'sf';
    case FANTASTIQUE = 'fantastique';
    case FANTASY     = 'fantasy';
    case HYBRIDE     = 'hybride';
    case AUTRE       = 'autre';
    case MAINSTREAM  = 'mainstream';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INCONNU     => 'Inconnu',
            self::SF          => 'Science-fiction',
            self::FANTASTIQUE => 'Fantastique',
            self::FANTASY     => 'Fantasy',
            self::HYBRIDE     => 'Hybride',
            self::AUTRE       => 'Autre',
            self::MAINSTREAM  => 'Blanche / Mainstream',
        };
    }
}
