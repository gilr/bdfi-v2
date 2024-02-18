<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum AwardCategoryType: string implements HasLabel {
    case ROMAN      = 'roman';
    case NOVELLA    = 'novella';
    case NOUVELLE   = 'nouvelle';
    case RECUEIL    = 'recueil';
    case ANTHOLOGIE = 'anthologie';
    case TEXTE      = 'texte';
    case AUTEUR     = 'auteur';
    case SPECIAL    = 'special';

    static function getOrder() {
        // Fourni dans l'ordre de l'énuméré, une liste de ses différentes valeurs au format : 'roman', 'novella', ...
        $values = array_column(AwardCategoryType::cases(), 'value');
        return "'" . implode ("' , '", $values) . "'";
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::AUTEUR     => 'Auteur',
            self::ROMAN      => 'Roman',
            self::NOVELLA    => 'Novella / Court roman',
            self::NOUVELLE   => 'Nouvelle',
            self::TEXTE      => 'Oeuvre / Texte / ouvrage',
            self::ANTHOLOGIE => 'Anthologie',
            self::RECUEIL    => 'Recueil',
            self::SPECIAL    => 'Prix spécial',
        };
    }
    public function getShortLabel(): ?string
    {
        return match ($this) {
            self::AUTEUR     => 'Auteur',
            self::ROMAN      => 'Roman',
            self::NOVELLA    => 'Novella',
            self::NOUVELLE   => 'Nouvelle',
            self::TEXTE      => 'Oeuvre',
            self::ANTHOLOGIE => 'Anthologie',
            self::RECUEIL    => 'Recueil',
            self::SPECIAL    => 'Prix spécial',
        };
    }
}
