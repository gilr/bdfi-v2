<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum IsNovelization: string implements HasLabel {
    case NON      = 'non';
    case FILM     = 'film';
    case SCENARIO = 'scenario';
    case TV       = 'tv';
    case JEUX     = 'jeux';
    case JDR      = 'jdr';
    case BD       = 'autre';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NON      => 'Non',
            self::FILM     => 'de film',
            self::SCENARIO => 'de scenario de film',
            self::TV       => 'de série télévisée',
            self::JEUX     => 'de jeux vidéo',
            self::JDR      => 'de jeux de table, JDR...',
            self::BD       => 'de BD',
        };
    }
}
