<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum CycleType: string implements HasLabel {
    case SERIE = 'serie';
    case CYCLE = 'cycle';
    case UNIVERS = 'univers';
    case FEUILLETON = 'feuilleton';
    case AUTRE = 'autre';


    public function getLabel(): ?string
    {
        return match ($this) {
            self::SERIE => 'Série',
            self::CYCLE => 'Cycle',
            self::UNIVERS => 'Univers',
            self::FEUILLETON => 'Feuilleton',
            self::AUTRE => 'Autre',
        };
    }
}
