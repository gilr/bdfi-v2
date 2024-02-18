<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum AnnouncementType: string implements HasLabel {
    case MERCI         = 'remerciement';
    case REFERENCEMENT = 'annonce_contenu';
    case EVOL_SITE     = 'annonce_site';
    case POINT_HISTO   = 'point_histo';
    case POINT_AIDES   = 'point_aides';
    case POINT_STATS   = 'point_stats';
    case CONSECRATION  = 'consecration';
    case AUTRE         = 'autre';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MERCI         => 'Remerciement',
            self::REFERENCEMENT => 'Référencement',
            self::EVOL_SITE     => 'Evolution site',
            self::POINT_HISTO   => 'Point historique',
            self::POINT_AIDES   => 'Point sur les aides',
            self::POINT_STATS   => 'Point statistique',
            self::CONSECRATION  => 'Consecration',
            self::AUTRE         => 'Autre',
        };
    }
}
