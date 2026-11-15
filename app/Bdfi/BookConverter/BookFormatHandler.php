<?php

namespace App\Bdfi\BookConverter;

/**
 * Un handler = un format de fichier, dans les deux sens.
 * Le format pivot est la liste de livres (array) décrite dans BookValidator.
 */
interface BookFormatHandler
{
    /** @return string[] extensions gérées, en minuscules (ex : ['col', 'utf8']) */
    public static function extensions(): array;

    /**
     * Fichier -> liste de livres normalisés.
     * @param array $options options de lecture (ex : ['encoding' => 'auto']), ignorées si sans objet
     */
    public function import(string $path, array $options = []): array;

    /** Liste de livres -> contenu au format du handler (pour les exports depuis le site) */
    public function export(array $books): string;
}
