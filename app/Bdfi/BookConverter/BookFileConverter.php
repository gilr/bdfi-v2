<?php

namespace App\Bdfi\BookConverter;

use Illuminate\Support\Facades\File;

/*

 -- Fichiers utiles à compléter et faire évoluer en fonction des besoins
 ImportBooks.php        : commande console d'import
 BookFileConverter.php  : Conversion d'un fichier d'entrée en JSON "livres"
 BookImporter.php       : logique d'import en base

 -- Fichiers outils définitifs
 Exceptions.hph         : Exceptions spécifiques
 ImportInteractor.php   : Abstraction des interactions avec l'opérateur
 ConsoleInteractor.php  : interaction dans le cas commande console
 ImportOptions.php      : Définitions des options d'import
*/

/* DANS CE FICHIER :
    rapatrier l'existant, faire évoluer et corriger
    en fonction du format d'entrée (BBCode, fichier .col, fichier CSV, fichier Excel...)
    Voir si utile de conserver un format pivot "array", et plutôt avoir le pivot = JSON
    Tout ce qui est dans App/Transformers et App/Transformers/Type
     => faire évoluer en mieux, en utilisant Claude

    => permettre de multiples formats d'entrées possibles (BBCode, fichier .col, fichier CSV, fichier Excel...)
    Voir si utile de conserver un format pivot "array" (qui servait pour aller de format A à format B via A->toArray puis B.fromArray)
     et plutôt finalement aller vers le format pivot JSON
   + garder la possibilité d'appeler ces utilitaires de conversion partout dans le site (pour faire des exports dans certains formats)
*/

/**
 * Convertit le fichier d'entrée (format source à définir : CSV, XML, TXT...) en JSON "Livres".
 *
 * Structure JSON cible (hypothèse, à ajuster) :
 * [
 *   {
 *     "titre": "Fondation",
 *     "auteurs": ["Isaac Asimov"],
 *     "editeur": "Gallimard",
 *     "collection": "Folio SF",
 *     "serie": "Cycle de Fondation",      // optionnel
 *     "isbn": "978-2-07-...",
 *     "annee": 1951
 *   }
 * ]
 */
final class BookFileConverter
{
    /** @return string chemin du JSON généré */
    public function convert(string $inputPath, string $ext, ?string $outputPath = null): string
    {
        if (!File::exists($inputPath)) {
            // Erreur incompréhensible, l'existence du fichier est vérifiée dans ImportBook
            throw new \InvalidArgumentException("Fichier introuvable : {$inputPath}");
        }

        $outputPath ??= storage_path('app/import/livres_' . date('Ymd_His') . '.json');
        File::ensureDirectoryExists(dirname($outputPath));

        // TODO : parser $inputPath et alimenter $books (une entrée par livre)
        // foreach ($this->parse($inputPath) as $row) { $books[] = [...]; }

        $books = [];

        echo "conversion depuis " . $inputPath . "\n";
        $books = match ($ext) {
//            'xls'   => $this->fromExcel($inputPath),
//            'xlsx'  => $this->fromExcel($inputPath),
            'csv'   => (new CsvConverter())->convert($inputPath),
            'col'   => (new ColConverter())->convert($inputPath),
//            'bbcode'=> $this->fromBBcode($inputPath),
//            'utf8'  => $this->fromUtf8($inputPath),
            default => throw new \Exception("Extension {$ext}  de {$inputPathinconnue}."),
        };

        echo "converti depuis format " . $ext . "\n";
        File::put($outputPath, json_encode($books, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $outputPath;
    }

    /** @return array<int, array<string, mixed>> */
    public function load(string $jsonPath): array
    {
        return json_decode(File::get($jsonPath), true, flags: JSON_THROW_ON_ERROR);
    }

}
