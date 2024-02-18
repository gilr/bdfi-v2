<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum AuthorGender: string implements HasLabel {
    case F = 'F';
    case H = 'H';
    case IEL = 'IEL';
    case INCONNU = '?';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::F => 'Femme',
            self::H => 'Homme',
            self::IEL => 'Non-binaire',
            self::INCONNU => 'Inconnu',
        };
    }
}
