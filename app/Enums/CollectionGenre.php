<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum CollectionGenre: string implements HasLabel {
    case SF          = 'sf';
    case FANTASY     = 'fantasy';
    case FANTASTIQUE = 'fantastique';
    case GORE        = 'hybride';
    case POLICIER    = 'policier';
    case AUTRE       = 'autre';
    case NA          = 'na';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SF          => 'Science-fiction',
            self::FANTASY     => 'Fantasy',
            self::FANTASTIQUE => 'Fantastique',
            self::GORE        => 'Gore',
            self::POLICIER    => 'Policier',
            self::AUTRE       => 'Autre',
            self::NA          => 'N/A',
        };
    }
}
