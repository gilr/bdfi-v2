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

    /**
     * Types de texte admis pour le contenu principal d'une publication de ce type (le premier est le défaut).
     * Une publication "fiction courte" ne peut pas avoir un roman, un recueil ou une anthologie pour contenu principal, etc.
     * "inconnu" est toujours admis : type de texte non déterminé.
     *
     * @return TitleType[]
     */
    public function mainContentTypes(): array
    {
        $types = match ($this) {
            self::ROMAN       => [TitleType::ROMAN, TitleType::FIXUP, TitleType::THEATRE, TitleType::LIVREJEU, TitleType::BD],
            self::FICTION     => [TitleType::NOUVELLE, TitleType::NOVELLA, TitleType::SHORTSHORT, TitleType::POEME, TitleType::CHANSON,
                                  TitleType::THEATRE, TitleType::SCENARIO, TitleType::RADIO],
            self::RECUEIL     => [TitleType::RECUEIL, TitleType::ANTHO, TitleType::CHRONIQUES],
            self::OMNIBUS     => [TitleType::OMNIBUS],
            self::PERIODIQUE  => [TitleType::MAGAZINE],
            self::NON_FICTION => [TitleType::ESSAI, TitleType::GUIDE, TitleType::BIO, TitleType::BIBLIO, TitleType::ARTICLE, TitleType::LETTRE, TitleType::BD],
        };

        return [...$types, TitleType::INCONNU];
    }

    /**
     * Un extrait (texte de variante "extrait", quel que soit son type) peut être le contenu principal : fiction courte.
     * Le texte complet dont il est tiré se rattache ensuite dans le formulaire des variantes.
     */
    public function allowsExtract(): bool
    {
        return $this === self::FICTION;
    }

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
