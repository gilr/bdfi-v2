<?php

namespace App\Bdfi\Import;

final class PreparedImport
{
    /**
     * @param array<int, array<string, mixed>> $books  livres normalisés
     * @param array<int, array<int, array{attribute: string, message: string, value: mixed}>> $errors
     *        n° de livre (1-based) => erreurs de validation (champ, message, valeur fautive)
     * @param string[] $warnings  écarts entre le fichier source et la base (ex. éditeur/collection renommés
     *        en base mais pas dans l'en-tête du fichier COL), sans gravité pour l'import (qui se base sur le sigle)
     */
    public function __construct(
        public readonly string $sourcePath,
        public readonly string $extension,
        public readonly string $jsonPath,
        public readonly array $books,
        public readonly array $errors,
        public readonly array $warnings = [],
    ) {}
}
