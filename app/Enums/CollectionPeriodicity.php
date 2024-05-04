<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum CollectionPeriodicity: string implements HasLabel {
    case NA             = 'n-a';
    case QUOTIDIEN      = 'quotidien';
    case HEBDO          = 'hebdo';
    case BIMENSUEL      = 'bimensuel';
    case MENSUEL        = 'mensuel';
    case BIMESTRIEL     = 'bimestriel';
    case TRIMESTRIEL    = 'trimestriel';
    case SEMESTRIEL     = 'semestriel';
    case ANNUEL         = 'annuel';
    case APERIODIQUE    = 'aperiodique';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NA             => 'Non applicable',
            self::QUOTIDIEN      => 'Quotidien',
            self::HEBDO          => 'Hebdomadaire',
            self::BIMENSUEL      => 'Bimensuel',
            self::MENSUEL        => 'Mensuel',
            self::BIMESTRIEL     => 'Bimestriel',
            self::TRIMESTRIEL    => 'Trimestriel',
            self::SEMESTRIEL     => 'Semestriel',
            self::ANNUEL         => 'Annuel',
            self::APERIODIQUE    => 'Apériodique',
        };
    }
}
