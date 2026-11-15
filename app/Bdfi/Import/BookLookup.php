<?php

namespace App\Bdfi\Import;

use App\Enums\PublicationFormat;
use App\Models\Author;
use App\Models\Collection;
use App\Models\Cycle;
use App\Models\Publication;
use App\Models\Publisher;
use App\Models\Title;

/**
 * Règles de recherche en base, UNIQUE source de vérité pour BookImporter ET BookAnalyzer.
 * Lecture seule. NB : sensibilité à la casse/accents = collation de vos colonnes.
 *
 * Schéma (migrations) : authors.name = NOM de famille, authors.first_name = prénom (nullable).
 * titles.name = libellé du titre (et non "title").
 * Une collection peut avoir jusqu'à 3 éditeurs (publisher_id, publisher2_id, publisher3_id).
 */
final class BookLookup
{
    /** @var array<string, int> clé d'auteur => id en base (auteurs trouvés seulement), pour publicationExistsWithoutIsbn */
    private array $authorIdCache = [];

    public static function norm(?string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) $value));
    }

    public static function key(string $value): string
    {
        return mb_strtolower(self::norm($value));
    }

    /**
     * ISBN réel, ou '' : "-" (pas d'ISBN) et "?" (ISBN inconnu) ne sont pas des ISBN et ne doivent jamais
     * servir à rapprocher deux publications (doublons, édition d'origine).
     */
    public static function realIsbn(?string $isbn): string
    {
        $isbn = self::norm($isbn);

        return preg_match('/\d/', $isbn) ? $isbn : '';
    }

    /** Valeur à stocker dans publications.isbn : l'ISBN, "-" (pas d'ISBN), sinon "?" (inconnu, y compris vide) */
    public static function storedIsbn(?string $isbn): string
    {
        $isbn = self::norm($isbn);

        return $isbn === '-' ? '-' : (self::realIsbn($isbn) !== '' ? $isbn : '?');
    }

    /**
     * Auteurs au format canonique ({author_id?, auteur_prenom, auteur_nom}) => liste normalisée et dédoublonnée.
     * author_id (optionnel) désigne un auteur existant : il prime sur le nom, qui n'est plus qu'indicatif.
     * @return array<int, array{id: ?int, first: string, last: string, key: string, label: string, role: string}>
     */
    public static function authors(array $raw): array
    {
        $out = [];

        foreach ($raw as $a) {
            $first = self::norm($a['auteur_prenom'] ?? '');
            $last = self::norm($a['auteur_nom'] ?? '');
            $id = self::id($a['author_id'] ?? null);

            if ($id === null && $first === '' && $last === '') {
                continue;
            }

            // Avec un id, deux homonymes restent distincts ; sans id, l'identité est le nom
            $key = $id !== null ? "id:{$id}" : self::key($first . '|' . $last);
            $role = ($a['auteur_role'] ?? 'author') === 'editor' ? 'editor' : 'author';   // AuthorPublicationRole
            $label = trim("{$first} {$last}") ?: "auteur #{$id}";
            $out[$key] ??= ['id' => $id, 'first' => $first, 'last' => $last, 'key' => $key, 'label' => $label, 'role' => $role];
        }

        return array_values($out);
    }

    /**
     * Cycles d'un texte du sommaire : liste "series" (format COL : sous-cycles compris), sinon "serie" seule.
     * cycle_id (optionnel) désigne un cycle existant : il prime sur le nom.
     * @return array<int, array{id: ?int, name: string, number: string, parent: string}> noms normalisés, dédoublonnés ;
     *         parent : nom du cycle parent ('' si aucun)
     */
    public static function series(array $text): array
    {
        $raw = $text['series'] ?? [['serie' => $text['serie'] ?? '', 'num_serie' => $text['num_serie'] ?? '']];
        $out = [];

        foreach ($raw as $s) {
            $name = self::norm($s['serie'] ?? '');
            $id = self::id($s['cycle_id'] ?? null);
            if ($name !== '' || $id !== null) {
                $out[$id !== null ? "id:{$id}" : self::key($name)] ??= [
                    'id'     => $id,
                    'name'   => $name,
                    'number' => self::norm($s['num_serie'] ?? ''),
                    'parent' => self::norm($s['parent'] ?? ''),
                ];
            }
        }

        return array_values($out);
    }

    /** Identifiant optionnel du format pivot : entier positif, sinon null */
    private static function id(mixed $value): ?int
    {
        return (is_int($value) || (is_string($value) && ctype_digit($value))) && (int) $value > 0 ? (int) $value : null;
    }

    // ------------------------------------------------------------------ Résolution : id contrôlé, sinon nom
    //
    // Un id du format pivot (author_id, title_id, cycle_id, publisher_id, collection_id) est un raccourci, pas une
    // vérité : l'élément désigné n'est retenu que s'il satisfait TOUJOURS les mêmes critères que la recherche par nom
    // (il a pu "bouger" depuis l'analyse). Sinon, l'écart est noté (idMismatches) et la recherche complète par nom
    // s'applique, exactement comme sans id.

    /** @var array<string, array{type: string, id: int, label: string}> ids qui ne correspondent plus (clé "type#id") */
    private array $idMismatches = [];

    /** Ids fournis qui ne correspondaient plus (élément absent, supprimé, ou critères différents) */
    public function idMismatches(): array
    {
        return array_values($this->idMismatches);
    }

    public function resetIdMismatches(): void
    {
        $this->idMismatches = [];
    }

    private function noteIdMismatch(string $type, int $id, string $label): null
    {
        $this->idMismatches["{$type}#{$id}"] ??= ['type' => $type, 'id' => $id, 'label' => $label];

        return null;
    }

    /** Auteur (élément de authors()) : id contrôlé (même nom, même prénom), sinon recherche par nom */
    public function findAuthor(array $a): ?Author
    {
        if ($a['id'] !== null) {
            $author = $this->authorQuery($a['first'], $a['last'])->whereKey($a['id'])->first();
            if ($author) {
                return $author;
            }
            $this->noteIdMismatch('auteur', $a['id'], $a['label']);
        }

        return $this->author($a['first'], $a['last']);
    }

    /**
     * Titre d'un texte du sommaire : title_id contrôlé (mêmes critères que title() : libellé, titre VO,
     * traducteurs, ensemble exact d'auteurs), sinon recherche par title().
     */
    public function findTitle(array $text, array $authorIds): ?Title
    {
        $name = self::norm($text['titre'] ?? '');
        $titleVo = self::norm($text['titre_vo'] ?? '');
        $translators = self::translators($text);

        if (($id = self::id($text['title_id'] ?? null)) !== null) {
            $expected = collect($authorIds)->sort()->values()->all();
            $title = $this->titleQuery($name, $titleVo, $translators)->whereKey($id)->with('authors:id')->first();

            if ($title && $title->authors->pluck('id')->sort()->values()->all() === $expected) {
                return $title;
            }
            $this->noteIdMismatch('titre', $id, $name);
        }

        return $this->title($name, $authorIds, $titleVo, $translators);
    }

    /** Cycle (élément de series()) : id contrôlé (même nom), sinon recherche par nom */
    public function findCycle(array $serie): ?Cycle
    {
        if ($serie['id'] !== null) {
            $cycle = Cycle::whereKey($serie['id'])->where('name', $serie['name'])->first();
            if ($cycle) {
                return $cycle;
            }
            $this->noteIdMismatch('cycle', $serie['id'], $serie['name']);
        }

        return $this->cycle($serie['name']);
    }

    /**
     * Livre annoté avec les ids de ce qui existe déjà en base (null = serait créé) : publisher_id, collection_id,
     * auteurs[].author_id, sommaire[].title_id, sommaire[].auteurs[].author_id, sommaire[].series[].cycle_id.
     * Mêmes règles que l'analyse. Sert à écrire un .to-add.json lisible et vérifiable.
     */
    public function withIds(array $book): array
    {
        $resolved = $this->resolve($book);
        $book = ['publisher_id' => $resolved['publisher']?->id, 'collection_id' => $resolved['collection']?->id]
            + array_diff_key($book, ['publisher_id' => 0, 'collection_id' => 0]);

        $book['auteurs'] = array_map(fn ($raw) => $this->withAuthorId($raw), $book['auteurs'] ?? []);

        if (isset($book['sommaire']) && is_array($book['sommaire'])) {
            $book['sommaire'] = array_map(function ($text) {
                $text['auteurs'] = array_map(fn ($raw) => $this->withAuthorId($raw), $text['auteurs'] ?? []);

                // Titre existant seulement si tous ses auteurs existent (sinon il serait forcément créé)
                $ids = array_column($text['auteurs'], 'author_id');
                $titleId = in_array(null, $ids, true) ? null : $this->findTitle($text, $ids)?->id;
                $text = ['title_id' => $titleId] + array_diff_key($text, ['title_id' => 0]);

                if (isset($text['series']) && is_array($text['series'])) {
                    $text['series'] = array_map(function ($raw) {
                        $serie = self::series(['series' => [$raw]])[0] ?? null;

                        return ['cycle_id' => $serie ? $this->findCycle($serie)?->id : null] + array_diff_key($raw, ['cycle_id' => 0]);
                    }, $text['series']);
                }

                return $text;
            }, $book['sommaire']);
        }

        return $book;
    }

    /** Entrée d'auteur du format pivot, annotée de son author_id (null = serait créé) */
    private function withAuthorId(array $raw): array
    {
        $a = self::authors([$raw])[0] ?? null;

        return ['author_id' => $a ? $this->findAuthor($a)?->id : null] + array_diff_key($raw, ['author_id' => 0]);
    }

    /**
     * Cycles du texte pour lesquels le fichier indique un parent, alors qu'ils existent en base SANS parent.
     * Information seulement : l'import ne modifie jamais la hiérarchie des cycles existants.
     *
     * @return array<int, array{cycle: string, parent: string, id: int}>
     */
    public function cyclesMissingParent(array $text): array
    {
        $out = [];

        foreach (self::series($text) as $serie) {
            if ($serie['parent'] === '') {
                continue;
            }

            $cycle = $this->findCycle($serie);
            if ($cycle && $cycle->parent_id === null) {
                $out[] = ['cycle' => $cycle->name, 'parent' => $serie['parent'], 'id' => $cycle->id];
            }
        }

        return $out;
    }

    /** Textes du sommaire (un seul pour un roman) */
    public static function texts(array $book): array
    {
        return array_values($book['sommaire'] ?? []);
    }

    /**
     * Résout l'éditeur et la collection d'un livre.
     *  - "collection_sigle" renseigné (format COL) : collection par collections.sigle_bdfi,
     *    éditeur = celui de la collection ; à défaut, éditeur par publishers.sigle_bdfi (sans collection) ;
     *  - sinon : éditeur par son nom, puis collection (optionnelle) par son nom.
     * "collection_id" / "publisher_id" (optionnels) : utilisés s'ils sont toujours cohérents avec le livre
     * (cf. resolveByIds), sinon recherche complète ci-dessus.
     *
     * @return array{publisher: ?Publisher, collection: ?Collection, missing: ?string, label: string,
     *               reason: string, publisherName: string, collectionName: string}
     *         missing : null si tout est trouvé, sinon 'publisher' ou 'collection'
     */
    public function resolve(array $book): array
    {
        if (self::id($book['collection_id'] ?? null) !== null || self::id($book['publisher_id'] ?? null) !== null) {
            if ($resolved = $this->resolveByIds($book)) {
                return $resolved;
            }
        }

        $sigle = $this->effectiveSigle($book);

        if ($sigle !== '') {
            $collection = Collection::where('sigle_bdfi', $sigle)->first();

            if (!$collection) {
                // Sigle absent des collections : il peut désigner un éditeur seul (ouvrages V1 sans collection).
                $publisher = Publisher::where('sigle_bdfi', $sigle)->first();

                if ($publisher) {
                    return $this->resolved($publisher, null);
                }

                return $this->unresolved('collection', "sigle {$sigle}", "sigle introuvable (ni collection, ni éditeur) : {$sigle}", '', $sigle);
            }

            $publisher = $collection->publisher_id ? Publisher::find($collection->publisher_id) : null;

            if (!$publisher) {
                return $this->unresolved('publisher', "sigle {$sigle} (collection sans éditeur)", "collection {$sigle} sans éditeur", '', $collection->name);
            }

            return $this->resolved($publisher, $collection);
        }

        $publisherName = self::norm($book['editeur'] ?? '');
        $publisher = $this->publisher($publisherName);

        if (!$publisher) {
            return $this->unresolved('publisher', $publisherName, "éditeur introuvable : {$publisherName}", $publisherName, '');
        }

        // Collection optionnelle : sans nom, la publication n'est rattachée à aucune collection
        $collectionName = self::norm($book['collection'] ?? '');
        $collection = null;

        if ($collectionName !== '') {
            $collection = $this->collection($collectionName, $publisher);

            if (!$collection) {
                return $this->unresolved('collection', "{$publisherName} / {$collectionName}", "collection introuvable : {$collectionName}", $publisherName, $collectionName);
            }
        }

        return $this->resolved($publisher, $collection);
    }

    /**
     * Résolution par identifiants, CONTRÔLÉE : null (et écart noté) si un id ne correspond plus au livre.
     *  - collection_id : la collection doit porter le sigle du livre (format COL), ou à défaut son nom / nom court ;
     *    l'éditeur (publisher_id, ou celui de la collection) doit être l'un des éditeurs de la collection ;
     *  - publisher_id seul : l'éditeur doit porter le sigle du livre (éditeur sans collection, format COL),
     *    ou à défaut son nom ; la collection (optionnelle) est alors cherchée par son nom chez cet éditeur.
     */
    private function resolveByIds(array $book): ?array
    {
        $collectionId = self::id($book['collection_id'] ?? null);
        $publisherId = self::id($book['publisher_id'] ?? null);
        $sigle = $this->effectiveSigle($book);
        $collectionName = self::norm($book['collection'] ?? '');
        $publisherName = self::norm($book['editeur'] ?? '');

        $collection = null;
        if ($collectionId !== null) {
            $collection = Collection::find($collectionId);
            $matches = $collection && ($sigle !== ''
                ? self::key((string) $collection->sigle_bdfi) === self::key($sigle)
                : ($collectionName === '' || in_array(self::key($collectionName), [self::key($collection->name), self::key((string) $collection->shortname)], true)));

            if (!$matches) {
                return $this->noteIdMismatch('collection', $collectionId, $sigle !== '' ? "sigle {$sigle}" : $collectionName);
            }
        }

        $publisher = Publisher::find($publisherId ?? $collection?->publisher_id);
        if (!$publisher) {
            return $publisherId !== null ? $this->noteIdMismatch('éditeur', $publisherId, $publisherName) : null;
        }

        if ($collection) {
            $publishers = [$collection->publisher_id, $collection->publisher2_id, $collection->publisher3_id];

            return in_array($publisher->id, $publishers)
                ? $this->resolved($publisher, $collection)
                : $this->noteIdMismatch('éditeur', $publisher->id, "{$publisher->name} (hors collection #{$collection->id})");
        }

        // Éditeur seul : sigle d'éditeur (COL), sinon nom (l'éditeur d'époque d'un alias de sigle est accepté)
        $matches = $sigle !== ''
            ? self::key((string) $publisher->sigle_bdfi) === self::key($sigle)
            : ($publisherName === '' || in_array(self::key($publisherName), [self::key($publisher->name), self::key((string) $this->publisherNameOverride($book))], true));
        if (!$matches) {
            return $this->noteIdMismatch('éditeur', $publisher->id, $sigle !== '' ? "sigle {$sigle}" : $publisherName);
        }

        if ($sigle !== '' || $collectionName === '') {
            return $this->resolved($publisher, null);
        }

        $collection = $this->collection($collectionName, $publisher);

        return $collection
            ? $this->resolved($publisher, $collection)
            : $this->unresolved('collection', "{$publisher->name} / {$collectionName}", "collection introuvable : {$collectionName}", $publisher->name, $collectionName);
    }

    /** Sigle COL tel qu'il existe en base (après application des alias de SigleAliases). */
    private function effectiveSigle(array $book): string
    {
        $sigle = self::norm($book['collection_sigle'] ?? '');

        return SigleAliases::sigle($sigle);
    }

    /** Nom d'éditeur d'époque à stocker dans publications.publisher_name (null = aucun : nom actuel). */
    public function publisherNameOverride(array $book): ?string
    {
        $sigle = self::norm($book['collection_sigle'] ?? '');

        return $sigle === '' ? null : SigleAliases::publisherName($sigle);
    }

    private function resolved(Publisher $publisher, ?Collection $collection): array
    {
        return [
            'publisher' => $publisher, 'collection' => $collection, 'missing' => null, 'label' => '', 'reason' => '',
            'publisherName' => $publisher->name, 'collectionName' => $collection?->name ?? '',
        ];
    }

    private function unresolved(string $missing, string $label, string $reason, string $publisherName, string $collectionName): array
    {
        return [
            'publisher' => null, 'collection' => null, 'missing' => $missing, 'label' => $label, 'reason' => $reason,
            'publisherName' => $publisherName, 'collectionName' => $collectionName,
        ];
    }

    public function publisher(string $name): ?Publisher
    {
        return Publisher::where('name', $name)->first();
    }

    public function collection(string $name, Publisher $publisher): ?Collection
    {
        // Nom ou nom court, et rattachée à cet éditeur (en position 1, 2 ou 3).
        // Une collection sans éditeur n'est donc jamais trouvée.
        return Collection::where(fn ($q) => $q->where('name', $name)->orWhere('shortname', $name))
            ->where(fn ($q) => $q->where('publisher_id', $publisher->id)
                ->orWhere('publisher2_id', $publisher->id)
                ->orWhere('publisher3_id', $publisher->id))
            ->first();
    }

    public function author(string $first, string $last): ?Author
    {
        return $this->authorQuery($first, $last)->first();
    }

    /** Critères d'identité d'un auteur : nom, et prénom (vide = absent) */
    private function authorQuery(string $first, string $last)
    {
        return Author::where('name', $last)
            ->where(fn ($q) => $first === ''
                ? $q->whereNull('first_name')->orWhere('first_name', '')
                : $q->where('first_name', $first));
    }

    /** Traducteurs d'un texte du sommaire, en chaîne normalisée (le JSON peut fournir une chaîne ou une liste) */
    public static function translators(array $text): string
    {
        $value = $text['traducteurs'] ?? '';

        return self::norm(is_array($value) ? implode(', ', array_filter($value)) : $value);
    }

    /**
     * Titre existant : même libellé, même titre original, mêmes traducteurs, et associé EXACTEMENT à cet
     * ensemble d'auteurs (comme le faisait le Perl : une autre traduction est un autre titre).
     * Un texte sans auteur (section, chronique...) n'est jamais rapproché d'un titre existant.
     */
    public function title(string $title, array $authorIds, string $titleVo = '', string $translators = ''): ?Title
    {
        if ($authorIds === []) {
            return null;
        }

        $expected = collect($authorIds)->sort()->values()->all();

        return $this->titleQuery($title, $titleVo, $translators)
            ->whereHas('authors', fn ($q) => $q->whereIn('authors.id', $expected))
            ->with('authors:id')
            ->get()
            ->first(fn (Title $t) => $t->authors->pluck('id')->sort()->values()->all() === $expected);
    }

    /** Critères d'identité d'un titre, hors auteurs : libellé, titre VO, traducteurs (vide = vide) */
    private function titleQuery(string $title, string $titleVo, string $translators)
    {
        return Title::where('name', $title)
            ->where(fn ($q) => self::equalsOrEmpty($q, 'title_vo', $titleVo))
            ->where(fn ($q) => self::equalsOrEmpty($q, 'translators', $translators));
    }

    private static function equalsOrEmpty($query, string $column, string $value): void
    {
        if ($value === '') {
            $query->whereNull($column)->orWhere($column, '');
        } else {
            $query->where($column, $value);
        }
    }

    public function cycle(string $name): ?Cycle
    {
        return Cycle::where('name', $name)->first();
    }

    /**
     * Évite les doublons de publication si l'import est relancé : même ISBN ET même année de parution.
     * (Plusieurs rééditions peuvent partager un ISBN avec une couverture différente : l'ISBN seul ne suffit pas.)
     * Sans année connue, l'ISBN seul fait foi. À affiner (ISBN sans tirets...).
     */
    public function publicationExists(?string $isbn, ?string $approximateParution = null): bool
    {
        $isbn = self::realIsbn($isbn);   // "-" et "?" ne sont pas des ISBN

        if ($isbn === '') {
            return false;
        }

        $year = substr((string) $approximateParution, 0, 4);

        return Publication::where('isbn', $isbn)
            ->when(preg_match('/^\d{4}$/', $year), fn ($q) => $q->where('approximate_parution', 'like', $year . '-%'))
            ->exists();
    }

    /**
     * Doublon d'un livre SANS ISBN : même éditeur, même collection (ou aucune des deux côtés), même titre,
     * mêmes auteurs (ensemble exact, rôle ignoré), même année de parution et même format.
     * Le mois n'est pas comparé (trop approximatif). En cas de doute, mieux vaut ne pas insérer qu'insérer un doublon :
     * l'ISBN éventuel de la publication en base n'est pas regardé.
     */
    public function publicationExistsWithoutIsbn(array $book, Publisher $publisher, ?Collection $collection): bool
    {
        // Auteurs : tous doivent exister en base, sinon la publication ne peut pas y être
        $authorIds = [];
        foreach (self::authors($book['auteurs'] ?? []) as $a) {
            // Seuls les auteurs trouvés sont mis en cache : un auteur créé plus loin dans l'import sera retrouvé
            $id = $this->authorIdCache[$a['key']] ?? $this->findAuthor($a)?->id;
            if ($id === null) {
                return false;
            }
            $authorIds[] = $this->authorIdCache[$a['key']] = $id;
        }
        sort($authorIds);

        $year = substr((string) ($book['approximate_parution'] ?? ''), 0, 4);
        $format = self::norm($book['format'] ?? '') ?: PublicationFormat::INCONNU->value;   // défaut SQL

        return Publication::where('publisher_id', $publisher->id)
            ->where('name', self::norm($book['titre'] ?? ''))
            ->where('format', $format)
            // Année inconnue dans le fichier : correspond à une année inconnue en base (vide, "19??", "?-00"...)
            ->when(preg_match('/^\d{4}$/', $year),
                fn ($q) => $q->where('approximate_parution', 'like', $year . '-%'),
                fn ($q) => $q->where(fn ($q) => $q->whereNull('approximate_parution')
                    ->orWhereRaw("approximate_parution NOT REGEXP '^[0-9]{4}'")))
            ->when($collection,
                fn ($q) => $q->whereHas('collections', fn ($q) => $q->where('collections.id', $collection->id)),
                fn ($q) => $q->whereDoesntHave('collections'))
            ->with('authors:id')
            ->get()
            ->contains(fn (Publication $p) => $p->authors->pluck('id')->sort()->values()->all() === $authorIds);
    }

    /** Clé d'un livre sans ISBN pour repérer les doublons DANS le fichier (mêmes critères que publicationExistsWithoutIsbn) */
    public static function withoutIsbnKey(array $book, Publisher $publisher, ?Collection $collection): string
    {
        $authors = array_column(self::authors($book['auteurs'] ?? []), 'key');
        sort($authors);

        return implode('|', [
            $publisher->id, $collection?->id ?? '-', self::key($book['titre'] ?? ''), implode('+', $authors),
            substr((string) ($book['approximate_parution'] ?? ''), 0, 4), self::norm($book['format'] ?? '') ?: PublicationFormat::INCONNU->value,
        ]);
    }

    /** Première publication (la plus ancienne en base) portant cet ISBN : sert à retrouver l'édition d'origine */
    public function firstPublicationIdByIsbn(string $isbn): ?int
    {
        $isbn = self::realIsbn($isbn);

        return $isbn === '' ? null : Publication::where('isbn', $isbn)->orderBy('id')->value('id');
    }
}