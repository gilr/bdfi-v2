<?php

namespace App\Bdfi\Import;

final class AnalysisReport
{
    public int $total = 0;
    public int $publications = 0;                 // livres qui seraient importés

    /** @var array<int, string> n° de livre => raison */
    public array $skipped = [];

    /** @var array<string, int[]> éditeur => n° des livres concernés */
    public array $missingPublishers = [];

    /** @var array<string, int[]> "éditeur / collection" => n° des livres concernés */
    public array $missingCollections = [];

    /** @var array<int, int[]> n° des livres déjà présents en base (même ISBN) */
    public array $alreadyImported = [];

    /** @var array<string, string> clé => nom */
    public array $newAuthors = [];

    /** @var array<string, array{title: string, authors: string[]}> */
    public array $newTitles = [];

    /** @var array<string, string> clé => nom */
    public array $newCycles = [];

    /**
     * Cycles existant en base sans parent, alors que le fichier leur en indique un (information seulement).
     * @var array<string, array{cycle: string, parent: string, id: int, books: int[]}> clé => cycle
     */
    public array $cyclesMissingParent = [];

    /**
     * Ids fournis par le fichier qui ne correspondent plus (élément supprimé ou modifié) : recherche par nom appliquée.
     * @var array<int, array{type: string, id: int, label: string}>
     */
    public array $idMismatches = [];

    /** @var array<int, array{title: string, authors: string[], publisher: string, collection: string, number: string, isbn: string, reprints: int}> n° de livre => publication qui serait créée */
    public array $newPublications = [];

    /**
     * État sérialisable (Livewire, JSON...) : les listes longues sont tronquées, les totaux restent exacts.
     */
    public function toArray(int $maxItems = 500): array
    {
        $list = fn (array $items, bool $keepKeys = false) => [
            'count' => count($items),
            'items' => array_slice($keepKeys ? $items : array_values($items), 0, $maxItems, $keepKeys),
        ];

        return [
            'total'               => $this->total,
            'publications'        => $this->publications,
            'skipped'             => count($this->skipped),
            'alreadyImported'     => count($this->alreadyImported),
            'missingPublishers'   => $this->missingPublishers,
            'missingCollections'  => $this->missingCollections,
            'newAuthors'          => $list($this->newAuthors),
            'newTitles'           => $list($this->newTitles),
            'newCycles'           => $list($this->newCycles),
            'cyclesMissingParent' => array_values($this->cyclesMissingParent),
            'idMismatches'        => $this->idMismatches,
            'newPublications'     => $list($this->newPublications, keepKeys: true),   // clés = n° de livre
        ];
    }
}