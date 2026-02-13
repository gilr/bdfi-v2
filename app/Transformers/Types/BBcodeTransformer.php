<?php

namespace App\Transformers\Types;

use App\Transformers\FormatTransformer;
use Illuminate\Http\UploadedFile;

// Terminé dans les deux sens

class BBcodeTransformer implements FormatTransformer
{

    /**
     *   Transformation vers le format pivot
    **/
    public function toArray(array|string|UploadedFile $input): array
    {
        $content = is_array($input) ? implode("\n", $input) : $input;

        $blocks = $this->splitByYear($content);
        $books = $this->processBlocks($blocks);

        return $books->toArray();
    }

    /**
     *   Transformation depuis le format pivot
    **/
    public function fromArray(array $data): string
    {
        $grouped = collect($data)->groupBy('annee_parution');

        $output = $grouped->map(function ($books, $year) {
            $lines = ["[b]{$year}[/b]"];

            foreach ($books as $book) {
                $serie = $book['serie'] ? "({$book['serie']}) " : '';
                $titre = $book['titre'];
                $auteur = "{$book['auteurs'][0]['auteur_prenom']} {$book['auteurs'][0]['auteur_nom']}";
                $copyright = $book['sommaire'][0]['copyright'] ?? $year;
                $isbn = $book['isbn'] ?? '';
                $illu = $book['illustrateur_couv'] ?? '';
                $line = "- {$auteur} .. {$titre} {$serie}({$copyright}) - isbn : {$isbn}  couv. : {$illu}";

                $lines[] = $line;
            }

            // Ajout des couvertures pour cette année
            $covers = $books->filter(fn($b) => !empty($b['img_couv']))
                            ->map(fn($b) => $this->generateCoverBBCode($b['img_couv']))
                            ->implode('  ');

            if ($covers) {
                $lines[] = $covers;
            }

            return implode("\n", $lines);
        })->implode("\n\n");

        return $output;
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
            $books = $this->parseBooks($year, $block);

            if ($books->isEmpty()) {
                $this->warn("Aucun livre trouvé pour $year");
            }

            $books->each(function ($book, $index) use ($year, $covers, &$allBooks) {
                $book['annee_parution'] = $year;
                $book['img_couv'] = $covers[$index] ?? null;
                $allBooks->push($book);
//                $this->line("--- Livre : {$book['titre']} ({$book['auteur_nom']})");
            });
        }

        return $allBooks;
    }

    /**
     * Traitement du bloc d'ouvrages
     **/
    private function parseBooks(int $year, string $block): Collection
    {
        $lines = preg_split('/\r\n|\r|\n/', $block);
        $books = collect();

        foreach ($lines as $line) {
            $book = $this->parseBookLine($year, $line);

            if ($book) {
                $books->push($book);
            }
        }
        return $books;
    }

    /**
    * Analyse une ligne de livre et retourne un tableau associatif ou null
    */
    private function parseBookLine(int $year, string $line): ?array
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
            'auteurs' => [
                "0" => [
                    'auteur_prenom' => $prenom,
                    'auteur_nom' => $nom,
                ]
            ],
            'titre' => $titre,
            'serie' => $serie,
            'parution' => $year,
            'isbn' => $isbn,
            'illustrateur_couv' => $illustrateur,
            'sommaire' => [
                [
                    'hors_genres' => $horsGenres,
                    'auteurs' => [
                        "0" => [
                            'auteur_prenom' => $prenom,
                            'auteur_nom' => $nom,
                        ]
                    ],
                    'auteur_prenom' => $prenom,
                    'auteur_nom' => $nom,
                    'titre' => $titre,
                    'serie' => $serie,
                    'copyright' => $copyright,
                ]
            ]
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

    protected function generateCoverBBCode(string $coverName): string
    {
        $initial = strtolower($coverName[0]);
        $normal = "http://www.bdfi.info/couvs/{$initial}/{$coverName}.jpg";
        $thumb = "http://www.bdfi.info/vignettes/{$initial}/v_{$coverName}.jpg";

        return "[url={$normal}][img]{$thumb}[/img][/url]";
    }
}

