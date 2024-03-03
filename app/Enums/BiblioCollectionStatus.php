<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum BiblioCollectionStatus: string implements HasLabel {
    case EN_COURS       = 'en_cours';
    case QUASI_OK       = 'quasi_ok';
    case TERMINEE       = 'terminee';
    case EN_PAUSE       = 'en_pause';
    case CACHEE         = 'cachee';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::EN_COURS       => 'Recherchée',
            self::QUASI_OK       => 'Quasi complète',
            self::TERMINEE       => 'Complète',
            self::EN_PAUSE       => 'En pause',
            self::CACHEE         => 'Cachée',
        };
    }
    public function getDescription(): ?string
    {
        return match ($this) {
            self::EN_COURS       => 'La collection est recherchée',
            self::QUASI_OK       => 'Presque terminée, ou quelque ouvrages à remplacer',
            self::TERMINEE       => 'La collection est considérée terminée',
            self::EN_PAUSE       => 'La collection est marquée en pause, mais reste visible',
            self::CACHEE         => 'Collection et ouvrages ne sont plus taggés sur le site',
        };
    }
}
