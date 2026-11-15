<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

// État d'un ouvrage possédé (table user_publication, colonne status)
enum BiblioPublicationStatus: string implements HasLabel {
    case POSSEDE        = 'possede';
    case ABIME          = 'abime';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::POSSEDE        => 'Possédé',
            self::ABIME          => 'Possédé mais abîmé',
        };
    }
    public function getDescription(): ?string
    {
        return match ($this) {
            self::POSSEDE        => 'Ouvrage possédé, en état suffisamment bon',
            self::ABIME          => 'Ouvrage possédé, mais dans un état abîmé ou limite (à remplacer)',
        };
    }
}
