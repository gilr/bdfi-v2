<?php

namespace App\Enums;
use Filament\Support\Contracts\HasLabel;

enum TitleType: string implements HasLabel {
    case ROMAN        = 'novel';
    case NOVELLA      = 'novella';
    case NOUVELLE     = 'shortstory';
    case SHORTSHORT   = 'shortshort';
    case FIXUP        = 'fix-up';
    case FIXUPELEMENT = 'fix-up-element';
    case POEME        = 'poem';
    case CHANSON      = 'song';
    case THEATRE      = 'theatre';
    case SCENARIO     = 'scenario';
    case RADIO        = 'radio';
    case LETTRE       = 'letter';
    case PREFACE      = 'preface';
    case POSTFACE     = 'postface';
    case BIBLIO       = 'biblio';
    case BIO          = 'bio';
    case ESSAI        = 'essai';
    case GUIDE        = 'guide';
    case ARTICLE      = 'article';
    case SECTION      = 'section';
    case OMNIBUS      = 'omnibus';
    case RECUEIL      = 'collection';
    case ANTHO        = 'anthologie';
    case CHRONIQUES   = 'chroniques';
    case MAGAZINE     = 'magazine';
    case BD           = 'comics';
    case LIVREJEU     = 'gamebook';
    case JEU          = 'game';
    case INCONNU      = 'inconnu';
// Autres ?
// Omnibus, revue, magazine, journal ?
// excerpt ? (ou plutôt short fiction et nom = "Titre (extrait)")
// coverart ? interiorart ? backcovertart ?
// review ? inverview ? (pb auteurs...)

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ROMAN        => 'Roman',
            self::NOVELLA      => 'Novella',
            self::NOUVELLE     => 'Nouvelle',
            self::SHORTSHORT   => 'Short short',
            self::FIXUP        => 'Fix-up',
            self::FIXUPELEMENT => 'Complément de Fix-up',
            self::POEME        => 'Poème',
            self::CHANSON      => 'Texte de chanson',
            self::THEATRE      => 'Pièce de théâtre',
            self::SCENARIO     => 'Scénario',
            self::RADIO        => 'Pièce radio',
            self::LETTRE       => 'Lettre',
            self::PREFACE      => 'Préface',
            self::POSTFACE     => 'Postface',
            self::BIBLIO       => 'Bibliographie',
            self::BIO          => 'Biographie',
            self::ESSAI        => 'Essai',
            self::GUIDE        => 'Guide',
            self::ARTICLE      => 'Article',
            self::SECTION      => 'Section',
            self::OMNIBUS      => 'Omnibus',
            self::RECUEIL      => 'Recueil',
            self::ANTHO        => 'Anthologie',
            self::CHRONIQUES   => 'Chroniques',
            self::MAGAZINE     => 'Magazine',
            self::BD           => 'bd',
            self::LIVREJEU     => 'Livre-jeu',
            self::JEU          => 'Jeu',
            self::INCONNU      => 'Inconnu',
        };
    }
}
