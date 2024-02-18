<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum PublicationContent: string implements HasLabel {
    case ROMAN       = 'roman';
    case FICTION     = 'fiction';
    case RECUEIL     = 'compilation';
    case OMNIBUS     = 'omnibus';
    case PERIODIQUE  = 'periodique';
    case NON_FICTION = 'non-fiction';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ROMAN       => 'Roman',
            self::FICTION     => 'Fiction courte',
            self::RECUEIL     => 'Compilation de textes',
            self::OMNIBUS     => 'Omnibus',
            self::PERIODIQUE  => 'Périodique',
            self::NON_FICTION => 'Non-fiction',
        };
    }
}
