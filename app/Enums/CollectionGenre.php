<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum CollectionGenre: string implements HasLabel {
    case SF          = 'sf';
    case FANTASY     = 'fantasy';
    case FANTASTIQUE = 'fantastique';
    case HYBRIDE     = 'hybride';
    case GORE        = 'gore';
    case POLICIER    = 'policier';
    case AUTRE       = 'autre';
    case NA          = 'na';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SF          => 'Science-fiction',
            self::FANTASY     => 'Fantasy',
            self::FANTASTIQUE => 'Fantastique',
            self::HYBRIDE     => 'Hybride',
            self::GORE        => 'Gore',
            self::POLICIER    => 'Policier',
            self::AUTRE       => 'Autre',
            self::NA          => 'Non applicable',
        };
    }
}
