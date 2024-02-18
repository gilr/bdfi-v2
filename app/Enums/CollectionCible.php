<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum CollectionCible: string implements HasLabel {
    case JEUNESSE  = 'jeunesse';
    case YA        = 'ya';
    case ADULTE    = 'adulte';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::JEUNESSE => 'Jeunesse',
            self::YA       => 'Young-Adult',
            self::ADULTE   => 'Adulte',
        };
    }
}
