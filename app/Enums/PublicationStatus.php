<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum PublicationStatus: string implements HasLabel {
    case PUBLIE      = 'paru';
    case ANNONCE     = 'annonce';
    case ABANDONNE   = 'abandon';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PUBLIE    => 'Paru',
            self::ANNONCE   => 'Annoncé',
            self::ABANDONNE => 'Jamais paru',
        };
    }
}

