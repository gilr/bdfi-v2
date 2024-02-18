<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum CollectionType: string implements HasLabel {
    case COLLECTION = 'collection';
    case GROUPE     = 'groupe';
    case REVUE      = 'revue';
    case FANZINE    = 'fanzine';
    case MAGAZINE   = 'magazine';
    case JOURNAL    = 'journal';
    case ANTHO_P    = 'antho-p';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::COLLECTION => 'Collection',
            self::GROUPE     => 'Groupe',
            self::REVUE      => 'Revue',
            self::FANZINE    => 'Fanzine',
            self::MAGAZINE   => 'Magazine',
            self::JOURNAL    => 'Journal',
            self::ANTHO_P    => 'Antho périodique',
        };
    }
}
