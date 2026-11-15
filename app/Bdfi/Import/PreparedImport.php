<?php

namespace App\Bdfi\Import;

final class PreparedImport
{
    /**
     * @param array<int, array<string, mixed>> $books  livres normalisés
     * @param array<int, array<int, array{attribute: string, message: string, value: mixed}>> $errors
     *        n° de livre (1-based) => erreurs de validation (champ, message, valeur fautive)
     */
    public function __construct(
        public readonly string $sourcePath,
        public readonly string $extension,
        public readonly string $jsonPath,
        public readonly array $books,
        public readonly array $errors,
    ) {}
}
