<?php

namespace App\Bdfi\Import;

use App\Bdfi\BookConverter\BookFormatRegistry;
use Illuminate\Support\Facades\File;

/**
 * Tout ce qui précède l'import : résolution sécurisée du chemin, choix du handler,
 * conversion, écriture du JSON, validation. Utilisable à l'identique en console et en web.
 */
final class BookImportPreparer
{
    public function __construct(
        private BookFormatRegistry $registry,
        private BookValidator $validator,
    ) {}

    /** @throws \InvalidArgumentException si chemin/extension invalides (autres exceptions : erreur de conversion) */
    public function prepare(string $file, array $options = []): PreparedImport
    {
        $path = $this->resolvePath($file);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $books = $this->registry->for($extension)->import($path, $options);

        $jsonPath = $extension === 'json' ? $path : $this->writeJson($path, $books);

        return new PreparedImport($path, $extension, $jsonPath, $books, $this->validator->validate($books));
    }

    /**
     * Écrit un JSON ne contenant que certains livres du fichier préparé.
     * Fichier : <nom du fichier source>.<suffixe>.json, dans le même dossier que la source.
     *
     * @param int[] $bookNumbers n° de livres (base 1, comme dans les rapports)
     * @param ?callable $transform appliqué à chaque livre avant écriture (ex. BookLookup::withIds)
     * @return string chemin du fichier écrit
     */
    public function writeSubset(PreparedImport $prepared, array $bookNumbers, string $suffix, ?callable $transform = null): string
    {
        $books = array_map(fn (int $n) => $prepared->books[$n - 1], $bookNumbers);

        if ($transform) {
            $books = array_map($transform, $books);
        }

        $path = dirname($prepared->sourcePath) . DIRECTORY_SEPARATOR
            . pathinfo($prepared->sourcePath, PATHINFO_FILENAME) . ".{$suffix}.json";

        File::put($path, json_encode(array_values($books), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $path;
    }

    private function resolvePath(string $file): string
    {
        $base = realpath(storage_path('app/import'))
            ?: throw new \InvalidArgumentException("Dossier d'import introuvable : " . storage_path('app/import'));

        $path = realpath($base . DIRECTORY_SEPARATOR . $file);

        if ($path === false || !is_file($path)) {
            throw new \InvalidArgumentException("Le fichier \"{$file}\" n'existe pas dans storage/app/import/.");
        }

        // Empêche ../../.env et équivalents
        if (!str_starts_with($path, $base . DIRECTORY_SEPARATOR)) {
            throw new \InvalidArgumentException('Fichier en dehors du dossier d\'import.');
        }

        return $path;
    }

    private function writeJson(string $sourcePath, array $books): string
    {
        $jsonPath = dirname($sourcePath) . DIRECTORY_SEPARATOR . pathinfo($sourcePath, PATHINFO_FILENAME) . '.converted.json';
        File::put($jsonPath, json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        return $jsonPath;
    }
}