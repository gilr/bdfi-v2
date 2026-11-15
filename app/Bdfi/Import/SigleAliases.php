<?php

namespace App\Bdfi\Import;

/**
 * Sigles COL qui désignent une collection/un éditeur existant sous un ancien nom.
 *
 *  - 'sigle'          : sigle à utiliser en base (collections.sigle_bdfi ou publishers.sigle_bdfi) ;
 *  - 'publisher_name' : valeur de publications.publisher_name (nom d'éditeur à l'époque de la parution),
 *                       affichée sur la fiche ouvrage à la place du nom actuel de l'éditeur.
 */
final class SigleAliases
{
    private const ALIASES = [
        'PP_TERR' => ['sigle' => 'PO_TERR', 'publisher_name' => 'Presses Pocket'],
        // 'PP_xxx' => ['sigle' => 'PO_xxx', 'publisher_name' => 'Presses Pocket'],
    ];

    /** Sigle tel qu'il existe en base. */
    public static function sigle(string $sigle): string
    {
        return self::ALIASES[$sigle]['sigle'] ?? $sigle;
    }

    /** Nom d'éditeur d'époque (null = aucun : nom actuel de l'éditeur). */
    public static function publisherName(string $sigle): ?string
    {
        return self::ALIASES[$sigle]['publisher_name'] ?? null;
    }

    /**
     * Inverse (export) : sigle d'origine d'une publication rangée sous $sigle avec ce nom d'éditeur d'époque.
     * Ex. PO_TERR + "Presses Pocket" => PP_TERR. Sans alias correspondant, $sigle est renvoyé tel quel.
     */
    public static function original(string $sigle, ?string $publisherName): string
    {
        if ($publisherName === null || $publisherName === '') {
            return $sigle;
        }

        foreach (self::ALIASES as $alias => $target) {
            if ($target['sigle'] === $sigle && $target['publisher_name'] === $publisherName) {
                return $alias;
            }
        }

        return $sigle;
    }
}