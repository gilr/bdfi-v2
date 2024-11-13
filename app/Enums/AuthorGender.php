<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum AuthorGender: string implements HasLabel {
    case INCONNU = '?';
    case F = 'F';
    case H = 'H';
    case IEL = 'IEL';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INCONNU => 'Inconnu',
            self::F => 'Femme',
            self::H => 'Homme',
            self::IEL => 'Non-binaire',
        };
    }
}
