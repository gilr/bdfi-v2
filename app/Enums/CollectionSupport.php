<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum CollectionSupport: string implements HasLabel {
    case PAPIER    = 'papier';
    case NUMERIQUE = 'numerique';
    case AUDIO     = 'audio';
    case MIXTE     = 'mixte';
    case AUTRE     = 'autre';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PAPIER    => 'Papier',
            self::NUMERIQUE => 'Numérique',
            self::AUDIO     => 'Audio',
            self::MIXTE     => 'Mixte',
            self::AUTRE     => 'Autre',
        };
    }
}
