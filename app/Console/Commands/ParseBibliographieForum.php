<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use App\Transformers\FormatTransformerManager;

class ParseBibliographieForum extends Command
{
    protected $signature = 'convert:forum-to-json {path=storage/app/biblio.txt}';
    protected $description = 'Transforme un fichier bibliographique BBCode en JSON';

    public function handle(FormatTransformerManager $manager): void
    {
        $content = $this->loadContent();

        if (is_null($content)) {
            return;
        }

        try {
            $transformer = $manager->get('bbcode');
            $json = $transformer->toArray($content);
        } catch (\Exception $e) {
            $this->error("Erreur de transformation : " . $e->getMessage());
            return;
        }

        $outputPath = storage_path('app/output.json');
        file_put_contents($outputPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("--- Transformation réussie : " . count($json) . " éléments extraits");
        $this->info("--- Fichier généré : $outputPath");
    }

    /**
     * Lecture source
     **/
    private function loadContent(): ?string
    {
        $path = base_path($this->argument('path'));

        if (!file_exists($path)) {
            $this->error("Fichier non trouvé : $path");
            return null;
        }

        return file_get_contents($path);
    }

    /**
     * Séparation par année
     **/
    private function splitByYear(string $content): Collection
    {
        preg_match_all('/\[b\](\d{4})\[\/b\](.*?)(?=\[b\]\d{4}\[\/b\]|$)/s', $content, $matches, PREG_SET_ORDER);

        return collect($matches)->mapWithKeys(function ($match) {
            return [$match[1] => trim($match[2])];
        });
    }

    /**
     * Traitement d'un bloc année
     **/
    private function processBlocks(Collection $blocks): Collection
    {
        $allBooks = collect();

        foreach ($blocks as $year => $block) {
//            $this->info("--- Traitement de l'année $year");

            $covers = $this->extractCovers($block);
            $books = $this->parseBooks($block);

            if ($books->isEmpty()) {
                $this->warn("Aucun livre trouvé pour $year");
            }

            $books->each(function ($book, $index) use ($year, $covers, &$allBooks) {
                $book['annee_parution'] = $year;
                $book['couverture'] = $covers[$index] ?? null;
                $allBooks->push($book);
//                $this->line("--- Livre : {$book['titre']} ({$book['auteur_nom']})");
            });
        }

        return $allBooks;
    }

    /**
     * Traitement du bloc d'ouvrages
     **/
    private function parseBooks(string $block): Collection
    {
        $lines = preg_split('/\r\n|\r|\n/', $block);
        $books = collect();

        foreach ($lines as $line) {
            $book = $this->parseBookLine($line);

            if ($book) {
                $books->push($book);
            }
        }
        return $books;
    }

    /**
    * Analyse une ligne de livre et retourne un tableau associatif ou null
    */
    private function parseBookLine(string $line): ?array
    {
        $line = trim(strip_tags($line));
        $horsGenres = false;

        if (preg_match('/^\[i\](.*)\[\/i\]$/', $line, $matches)) {
            $line = trim($matches[1]);
            $horsGenres = true;
        }

        if (!str_starts_with($line, '-')) {
            return null;
        }

        preg_match('/^- (.+?) \.\. (.+?) (?:\((.+?)\) )?\((\d{4}(?:\/\d{4})?)\) - isbn ?: ([\d\-]+)  couv\. ?: (.+)$/', $line, $m);

        if (!$m) {
            $this->warn("Ligne non reconnue : $line");
            return null;
        }

        [$_, $auteur, $titre, $serie, $copyright, $isbn, $illustrateur] = $m;
        $parts = explode(' ', $auteur);
        $prenom = array_shift($parts);
        $nom = implode(' ', $parts);

        return [
            'hors_genres' => $horsGenres,
            'auteur_prenom' => $prenom,
            'auteur_nom' => $nom,
            'titre' => $titre,
            'serie' => $serie,
            'copyright' => $copyright,
            'isbn' => $isbn,
            'illustrateur' => $illustrateur,
        ];
    }

    /**
     * Traitement du bloc des couvertures
     **/
    private function extractCovers(string $block): array
    {
        preg_match_all('/\[url=[^\]]*\/([^\/\]]+)\.jpg\]/i', $block, $matches);
        return $matches[1] ?? [];
    }
}
