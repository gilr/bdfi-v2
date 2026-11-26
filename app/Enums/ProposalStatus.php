<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/** Etat d'une proposition : envoyée (à étudier), puis acceptée ou refusée par un membre, ou retirée par son auteur */
enum ProposalStatus: string implements HasLabel
{
    case SUBMITTED = 'submitted';
    case ACCEPTED  = 'accepted';
    case REJECTED  = 'rejected';
    case WITHDRAWN = 'withdrawn';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SUBMITTED => 'En attente',
            self::ACCEPTED  => 'Acceptée',
            self::REJECTED  => 'Refusée',
            self::WITHDRAWN => 'Retirée',
        };
    }
}
