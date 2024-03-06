<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum PublicationStatus: string implements HasLabel {
    case PUBLIE      = 'paru';
    case ANNONCE     = 'annonce';
    case PROPOSE     = 'proposal';
    case ABANDONNE   = 'abandon';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PUBLIE    => 'Paru',
            self::ANNONCE   => 'Annoncé',
            self::PROPOSE   => 'Proposition membre',
            self::ABANDONNE => 'Jamais paru',
        };
    }
}

