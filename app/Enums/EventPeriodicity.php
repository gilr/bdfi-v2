<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum EventPeriodicity: string implements HasLabel {
    case UNIQUE     = 'unique';
    case ANNUEL     = 'annuel';
    case BIENNAL    = 'biennal';
    case IRREGULIER = 'irregulier';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::UNIQUE     => 'Unique',
            self::ANNUEL     => 'Annuel',
            self::BIENNAL    => 'Tous les deux ans',
            self::IRREGULIER => 'Irrégulier',
        };
    }
}
