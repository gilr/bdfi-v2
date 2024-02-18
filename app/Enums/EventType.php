<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum EventType: string implements HasLabel {
    case SALON = 'salon';
    case FESTIVAL = 'festival';
    case CONVENTION = 'convention';
    case EXPO = 'exposition';
    case FILM_FESTIVAL = 'film-festival';
    case AUTRE = 'autre';


    public function getLabel(): ?string
    {
        return match ($this) {
            self::SALON  => 'Salon',
            self::FESTIVAL  => 'Festival',
            self::CONVENTION => 'Convention',
            self::EXPO  => 'Exposition',
            self::FILM_FESTIVAL => 'Festival cinéma',
            self::AUTRE  => 'Autre',
        };
    }
}
