<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Nature d'une proposition de contributeur (table proposals). Seul l'ajout d'un ouvrage existe pour l'instant ;
 * viendront ensuite : correction d'un ouvrage, ajout/correction de sommaire, éditeur, collection...
 */
enum ProposalType: string implements HasLabel
{
    case PUBLICATION_ADD = 'publication_add';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PUBLICATION_ADD => "Ajout d'un ouvrage",
        };
    }
}
