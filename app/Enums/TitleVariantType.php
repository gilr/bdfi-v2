<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum TitleVariantType: string implements HasLabel {
    case PREMIER       = 'premier';
    case VIRTUEL       = 'virtuel';     // Le Virtuel est un premier jamais paru (permet d'attribuer le véritable auteur d'un texte)
    case EPISODE       = 'feuilleton';
    case EXTRAIT       = 'extrait';
    case SIGN          = 'signature';
    case TRAD          = 'traduction';
    case TITRE         = 'titre';
    case SIGNTRADTITRE = 'sign+trad+titre';
    case SIGNTRAD      = 'sign+trad';
    case SIGNTITRE     = 'sign+titre';
    case TRADTITRE     = 'trad+titre';
    case INCONNU       = 'inconnu';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PREMIER       => 'Première publi',
            self::VIRTUEL       => 'Auteur réel',
            self::EPISODE       => 'Episode feuilleton',
            self::EXTRAIT       => 'Extrait',
            self::SIGN          => 'Autre signature',
            self::TRAD          => 'Autre traduction',
            self::TITRE         => 'Autre titre',
            self::SIGNTRADTITRE => 'Signature, traduction, titre',
            self::SIGNTRAD      => 'Signature et traduction',
            self::SIGNTITRE     => 'Signature et titre',
            self::TRADTITRE     => 'Traduction et titre',
            self::INCONNU       => 'Traducteur inconnu',
        };
    }
    public function getDescription(): ?string
    {
        return match ($this) {
            self::PREMIER       => 'Titre parent, la première publication francophone connue ',
            self::VIRTUEL       => 'Titre parent virtuel, première publication, non publiée, mais avec le vrai nom d\'auteur',
            self::EPISODE       => 'Partie d\'un texte publié en feuilleton',
            self::EXTRAIT       => 'Partie du texte d\'origine publiée seule',
            self::SIGN          => 'Signature a été modifiée par rapport aux publications précédentes',
            self::TRAD          => 'Traduction différente ou modifiée par rapport aux publications précédentes',
            self::TITRE         => 'Titre modifié par rapport aux publications précédentes',
            self::SIGNTRADTITRE => 'Autre signature, autre traduction et autre titre par rapport aux publications précédentes',
            self::SIGNTRAD      => 'Autre signature et autre traduction par rapport aux publications précédentes',
            self::SIGNTITRE     => 'Autre signature et autre titre par rapport aux publications précédentes',
            self::TRADTITRE     => 'Autre traduction et autre titre par rapport aux publications précédentes',
            self::INCONNU       => 'Le traducteur de ce texte est inconnu',
        };
    }
}
