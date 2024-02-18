<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum AwardCategoryGenre: string implements HasLabel {
    case IMAGINAIRE  = 'imaginaire';
    case SF          = 'sf';
    case FANTASTIQUE = 'fantastique';
    case FANTASY     = 'fantasy';
    case HORREUR     = 'horreur';
    case MAINSTREAM  = 'mainstream';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::IMAGINAIRE  => 'Imaginaire',
            self::SF          => 'Science-fiction',
            self::FANTASTIQUE => 'Fantastique',
            self::FANTASY     => 'Fantasy',
            self::HORREUR     => 'Horreur',
            self::MAINSTREAM  => 'Blanche/Mainstream',
        };
    }
    public function getShortLabel(): ?string
    {
        return match ($this) {
            self::IMAGINAIRE  => 'Imaginaire',
            self::SF          => 'SF',
            self::FANTASTIQUE => 'Fantastique',
            self::FANTASY     => 'Fantasy',
            self::HORREUR     => 'Horreur',
            self::MAINSTREAM  => 'Mainstream',
        };
    }
}
