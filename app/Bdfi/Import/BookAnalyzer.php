<?php

namespace App\Bdfi\Import;

/**
 * Simule l'import SANS aucune écriture : uniquement des recherches (mises en cache pour les auteurs).
 * Les objets "à créer" sont dédoublonnés en mémoire, comme le ferait l'import réel.
 * Un livre ignoré à l'étape 1 ne contribue à aucune création.
 */
final class BookAnalyzer
{
    /** @var array<string, int|null> clé auteur => id en base (null = absent) */
    private array $authorCache = [];

    public function __construct(private BookLookup $lookup) {}

    /** @param array<int, array<string, mixed>> $books (clés d'origine conservées) */
    public function analyse(array $books): AnalysisReport
    {
        $r = new AnalysisReport();
        $r->total = count($books);
        $this->lookup->resetIdMismatches();
        $seenIsbns = [];   // ISBN des livres déjà retenus dans ce fichier
        $seenWithoutIsbn = [];   // clés des livres sans ISBN déjà retenus (BookLookup::withoutIsbnKey)

        foreach ($books as $index => $book) {
            $n = $index + 1;

            // ---- Information : cycles en base sans parent alors que le fichier en indique un ----
            // (tous les livres, y compris ceux déjà en base : c'est une information sur les cycles)
            foreach (BookLookup::texts($book) as $text) {
                foreach ($this->lookup->cyclesMissingParent($text) as $m) {
                    $key = BookLookup::key($m['cycle']) . '|' . BookLookup::key($m['parent']);
                    $r->cyclesMissingParent[$key] ??= $m + ['books' => []];
                    $r->cyclesMissingParent[$key]['books'][] = $n;
                }
            }

            // ---- Doublon d'abord (même ordre que BookImporter::run) ----
            // Doublon : déjà en base, ou déjà retenu plus haut dans ce fichier (l'import réel l'ignorerait aussi)
            $isbn = BookLookup::realIsbn($book['isbn'] ?? '');   // "-" / "?" : sans ISBN
            // (même ISBN et même année de parution : des rééditions peuvent partager un ISBN)
            $dupKey = $isbn . '|' . substr((string) ($book['approximate_parution'] ?? ''), 0, 4);
            if ($isbn !== '' && (isset($seenIsbns[$dupKey]) || $this->lookup->publicationExists($isbn, $book['approximate_parution'] ?? null))) {
                $r->alreadyImported[] = $n;
                $r->skipped[$n] = isset($seenIsbns[$dupKey]) ? 'ISBN en doublon dans le fichier' : 'publication déjà en base (ISBN + année)';
                continue;
            }

            // ---- Étape 1 : éditeur / collection / doublon ----
            $resolved = $this->lookup->resolve($book);

            if ($resolved['missing'] === 'publisher') {
                $r->missingPublishers[$resolved['label']][] = $n;
                $r->skipped[$n] = $resolved['reason'];
                continue;
            }

            if ($resolved['missing'] === 'collection') {
                $r->missingCollections[$resolved['label']][] = $n;
                $r->skipped[$n] = $resolved['reason'];
                continue;
            }

            // ---- Doublon d'un livre SANS ISBN : éditeur, collection, titre, auteurs, année, format ----
            // (après l'étape 1, qui donne l'éditeur et la collection ; même règle que BookImporter::run)
            $noIsbnKey = null;
            if ($isbn === '') {
                $noIsbnKey = BookLookup::withoutIsbnKey($book, $resolved['publisher'], $resolved['collection']);
                $inFile = isset($seenWithoutIsbn[$noIsbnKey]);
                if ($inFile || $this->lookup->publicationExistsWithoutIsbn($book, $resolved['publisher'], $resolved['collection'])) {
                    $r->alreadyImported[] = $n;
                    $r->skipped[$n] = $inFile
                        ? 'doublon dans le fichier (sans ISBN : éditeur, collection, titre, auteurs, année, format)'
                        : 'publication déjà en base (sans ISBN : éditeur, collection, titre, auteurs, année, format)';
                    continue;
                }
            }

            $publisherName = $resolved['publisherName'];
            $collectionName = $resolved['collectionName'];

            // ---- Étape 2 : auteurs du livre ----
            $this->registerAuthors(BookLookup::authors($book['auteurs']), $r);

            // ---- Étapes 2 (auteurs du texte), 3 (titre) et 4 (cycle), pour chaque texte du sommaire ----
            foreach (BookLookup::texts($book) as $textIndex => $text) {
                $info = $this->registerAuthors(BookLookup::authors($text['auteurs'] ?? []), $r);

                $titleName = BookLookup::norm($text['titre']);

                // title_id fourni : titre existant. Sinon, un auteur nouveau implique forcément un titre nouveau.
                $titleVo = BookLookup::norm($text['titre_vo'] ?? '');
                $translators = BookLookup::translators($text);
                $exists = (!empty($text['title_id']) || !$info['hasNew'])
                    && $this->lookup->findTitle($text, $info['ids']) !== null;

                if (!$exists) {
                    $keys = $info['keys'];
                    sort($keys);
                    // Identité d'un titre : libellé, auteurs, titre original, traducteurs ; un texte sans auteur est toujours nouveau
                    $identity = BookLookup::key($titleName) . '|' . implode(',', $keys)
                        . '|' . BookLookup::key($titleVo) . '|' . BookLookup::key($translators)
                        . ($keys === [] ? "|#{$n}-{$textIndex}" : '');
                    $r->newTitles[$identity] ??= [
                        'title'   => $titleName,
                        'authors' => $info['labels'],
                    ];
                }

                foreach (BookLookup::series($text) as $serie) {
                    if (!$this->lookup->findCycle($serie)) {
                        $r->newCycles[BookLookup::key($serie['name'])] ??= $serie['name'];
                    }
                }
            }

            // ---- Étape 5 : publication ----
            $r->publications++;
            if ($isbn !== '') {
                $seenIsbns[$dupKey] = true;
            } else {
                $seenWithoutIsbn[$noIsbnKey] = true;
            }
            $r->newPublications[$n] = [
                'title'      => BookLookup::norm($book['titre']),
                'authors'    => array_column(BookLookup::authors($book['auteurs']), 'label'),
                'publisher'  => $publisherName,
                'collection' => $collectionName,
                'number'     => BookLookup::norm($book['no_collection'] ?? ''),
                'isbn'       => BookLookup::norm($book['isbn'] ?? ''),
                'reprints'   => count($book['retirages'] ?? []),
            ];
        }

        $r->idMismatches = $this->lookup->idMismatches();

        return $r;
    }

    /**
     * Enregistre les auteurs absents en base dans le rapport.
     * @return array{ids: int[], keys: string[], labels: string[], hasNew: bool}
     */
    private function registerAuthors(array $authors, AnalysisReport $r): array
    {
        $info = ['ids' => [], 'keys' => [], 'labels' => [], 'hasNew' => false];

        foreach ($authors as $a) {
            if (!array_key_exists($a['key'], $this->authorCache)) {
                $this->authorCache[$a['key']] = $this->lookup->findAuthor($a)?->id;   // par id, sinon par nom
            }

            $id = $this->authorCache[$a['key']];
            $info['labels'][] = $a['label'];

            if ($id !== null) {
                $info['ids'][] = $id;
                $info['keys'][] = 'id:' . $id;
            } else {
                $r->newAuthors[$a['key']] ??= $a['label'];
                $info['keys'][] = 'new:' . $a['key'];
                $info['hasNew'] = true;
            }
        }

        return $info;
    }
}