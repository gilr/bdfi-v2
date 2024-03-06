<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum AudienceTarget: string implements HasLabel {
    case INCONNU = 'inconnu';
    case JEUNESSE = 'jeunesse';
    case YA = 'YA';
    case ADULTE = 'adulte';
    case ADULTEONLY = 'adulte-only';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INCONNU => 'Inconnu',
            self::JEUNESSE => 'Jeunesse',
            self::YA => 'YA',
            self::ADULTE => 'Adulte',
            self::ADULTEONLY => 'Reservé aux adultes (+18)',
        };
    }
}
