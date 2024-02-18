<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum AudienceTarget: string implements HasLabel {
    case JEUNESSE = 'jeunesse';
    case YA = 'YA';
    case ADULTE = 'adulte';
    case INCONNU = 'inconnu';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::JEUNESSE => 'Jeunesse',
            self::YA => 'YA',
            self::ADULTE => 'Adulte',
            self::INCONNU => 'Inconnu',
        };
    }
}
