<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum PublisherType: string implements HasLabel {
    case EDITEUR       = 'editeur';
    case MICROEDITEUR = 'micro-editeur';
    case AUTOEDITEUR  = 'autoediteur';
    case COMPTE_AUTEUR = 'compte-auteur';
    case AUTRE         = 'autre';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::EDITEUR       => 'Compte d\'éditeur',
            self::MICROEDITEUR  => 'Micro-éditeur',
            self::AUTOEDITEUR   => 'Auto-éditeur',
            self::COMPTE_AUTEUR => 'Compte d\'auteur',
            self::AUTRE         => 'Autre',
        };
    }
}

