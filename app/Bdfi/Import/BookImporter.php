<?php

namespace App\Bdfi\Import;

use App\Enums\AudienceTarget;
use App\Enums\CycleType;
use App\Enums\GenreStat;
use App\Enums\QualityStatus;
use App\Enums\TitleVariantType;
use App\Models\Author;
use App\Models\Collection;
use App\Models\Cycle;
use App\Models\Publication;
use App\Models\Publisher;
use App\Models\Reprint;
use App\Models\Title;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Stratégie transactionnelle :
 *  - dry-run   : transaction globale TOUJOURS rollbackée à la fin (les transactions par livre
 *                deviennent des savepoints). Détecte aussi les erreurs SQL, contrairement à l'analyse.
 *  - par livre : une transaction (ou savepoint) par livre ; commit seulement après les validations.
 *  - step-by-step : validation après chaque étape ; refus => rollback du livre entier, puis arrêt.
 *
 * Les règles de recherche sont dans BookLookup (partagées avec BookAnalyzer).
 * Les étapes 3 et 4 bouclent sur les textes du sommaire (un seul pour l'instant : cf. BookValidator).
 */
final class BookImporter
{

    /** groupe d'éditions (COL) => id de la publication d'origine importée dans ce run */
    private array $firstEditionByGroup = [];

    private array $stats = ['imported' => 0, 'skipped' => 0, 'already_imported' => 0, 'rolled_back' => 0, 'aborted' => false,
        // clé => {cycle, parent, id, books[]} : cycles en base sans parent, alors que le fichier en indique un
        'cycles_missing_parent' => [],
        // {type, id, label} : ids du fichier qui ne correspondaient plus (recherche par nom appliquée)
        'id_mismatches' => [],
        // {id, title, slug} : publications créées (commitées), dans l'ordre du fichier
        'created' => []];

    public function __construct(
        private ImportOptions $options,
        private ImportInteractor $ui,
        private BookLookup $lookup = new BookLookup(),
    ) {}

    /** @param array<int, array<string, mixed>> $books (clés d'origine conservées) */
    public function run(array $books): array
    {
        if ($this->options->dryRun) {
            DB::beginTransaction();
            $this->ui->warn('[DRY-RUN] Rien ne sera conservé en base.');
        }

        try {
            foreach ($books as $index => $book) {
                $label = sprintf('--- Livre #%d : %s', $index + 1, $book['titre']);

                // Information (tous les livres, comme l'analyse) : cycle en base sans parent, parent indiqué par le fichier
                $this->noteCyclesMissingParent($book, $index + 1);

                // Doublon (ISBN déjà en base, ou déjà importé plus haut dans ce fichier) :
                // simplement compté, et détaillé livre par livre seulement avec -v
                if ($this->lookup->publicationExists($book['isbn'] ?? null, $book['approximate_parution'] ?? null)) {
                    $this->stats['already_imported']++;
                    $this->ui->debug("{$label} : déjà en base (ISBN + année), ignoré");
                    continue;
                }

                // Sans ISBN : éditeur, collection, titre, auteurs, année et format (cf. BookLookup)
                if ($this->existsWithoutIsbn($book)) {
                    $this->stats['already_imported']++;
                    $this->ui->debug("{$label} : déjà en base (sans ISBN : éditeur, collection, titre, auteurs, année, format), ignoré");
                    continue;
                }

                $this->ui->info($label);
                $this->importBook($book);
            }
        } catch (UserAborted $e) {
            $this->stats['aborted'] = true;
            $this->ui->warn("Import interrompu par l'opérateur : " . $e->getMessage());
        } finally {
            if ($this->options->dryRun) {
                DB::rollBack();
                $this->ui->warn('[DRY-RUN] Rollback global effectué.');
            }
        }

        $this->stats['id_mismatches'] = $this->lookup->idMismatches();

        return $this->stats;
    }

    private function importBook(array $book): void
    {
        DB::beginTransaction();

        try {
            $texts = BookLookup::texts($book);

            // ---- Étape 1 ----
            [$publisher, $collection] = $this->step('1', 'Éditeur / Collection',
                fn () => $this->checkPublisherAndCollection($book),
                fn ($r) => "Éditeur #{$r[0]->id}, " . ($r[1] ? "collection #{$r[1]->id}" : 'sans collection'));

            // ---- Étape 2 : auteurs du livre + auteurs de chaque texte (clé => Author) ----
            $authors = $this->step('2', 'Auteurs',
                fn () => $this->findOrCreateAuthors($book, $texts),
                fn ($r) => 'Auteurs : ' . self::ids($r));

            // ---- Étape 3 : un titre par texte ----
            $titles = $this->step('3', 'Titre(s)',
                fn () => array_map(fn ($text) => $this->findOrCreateTitle($text, $authors, $book), $texts),
                fn ($r) => 'Titres : ' . self::ids($r));

            // ---- Étape 4 : cycle(s) de chaque texte (si série ; sous-cycles compris) ----
            $this->step('4', 'Série / Cycle',
                fn () => array_merge(...array_map(fn ($text, $title) => $this->findOrCreateCycles($text, $title), $texts, $titles)),
                fn ($r) => $r ? 'Cycles : ' . self::ids($r) : 'Pas de série');

            // ---- Étape 5 : publication + associations ----
            $publication = $this->step('5', 'Publication',
                fn () => $this->createPublication($book, $publisher, $collection, $authors, $titles),
                fn ($r) => "Publication #{$r->id}" . (($n = count($book['retirages'] ?? [])) ? " (+ {$n} retirage(s))" : ''));

            if ($this->options->bookByBook
                && !$this->ui->confirm("Valider (commit) le livre « {$book['titre']} » ?")) {
                throw new UserAborted("livre « {$book['titre']} » refusé");
            }

            DB::commit();
            $this->stats['imported']++;
            // Publications créées (en dry-run, elles disparaissent au rollback global final)
            $this->stats['created'][] = ['id' => $publication->id, 'title' => $publication->name, 'slug' => $publication->slug];
        } catch (SkipBook $e) {
            DB::rollBack();
            $this->stats['skipped']++;
            $this->ui->warn("Livre ignoré : {$e->getMessage()}");
        } catch (Throwable $e) {
            DB::rollBack();
            $this->stats['rolled_back']++;
            throw $e;
        }
    }

    /** Ids séparés par des virgules, suivis de "(création)" pour les éléments créés par cet import */
    private static function ids(iterable $models): string
    {
        return collect($models)->map(fn ($m) => $m->id . ($m->wasRecentlyCreated ? ' (création)' : ''))->implode(', ');
    }

    private function step(string $num, string $label, callable $action, callable $describe): mixed
    {
        $result = $action();
        $this->ui->info("  Étape {$num} ({$label}) : " . $describe($result));

        if ($this->options->stepByStep && !$this->ui->confirm("  Valider l'étape {$num} ?")) {
            throw new UserAborted("étape {$num} ({$label}) refusée");
        }

        return $result;
    }

    // ------------------------------------------------------------------ Étapes

    /** @return array{0: Publisher, 1: ?Collection} */
    private function checkPublisherAndCollection(array $book): array
    {
        // Éditeur et collection (par noms, ou par sigle de collection pour le format COL)
        $resolved = $this->lookup->resolve($book);

        if ($resolved['missing'] !== null) {
            throw new SkipBook($resolved['reason']);
        }

        return [$resolved['publisher'], $resolved['collection']];
    }

    /** @return array<string, Author> clé d'auteur (BookLookup::authors) => Author */
    private function findOrCreateAuthors(array $book, array $texts): array
    {
        $all = BookLookup::authors($book['auteurs']);
        foreach ($texts as $text) {
            array_push($all, ...BookLookup::authors($text['auteurs'] ?? []));
        }

        $map = [];
        foreach ($all as $a) {
            // ??= : la recherche/création n'a lieu qu'une fois par auteur, même présent plusieurs fois
            // author_id fourni : auteur existant (vérifié par BookValidator) ; sinon recherche par nom
            $map[$a['key']] ??= $this->lookup->findAuthor($a)
                ?? Author::create(['name' => $a['last'], 'first_name' => $a['first'] !== '' ? $a['first'] : null]);
        }

        return $map;
    }

    private function findOrCreateTitle(array $text, array $authors, array $book): Title
    {
        $textAuthors = array_map(fn ($a) => $authors[$a['key']], BookLookup::authors($text['auteurs'] ?? []));
        $authorIds = collect($textAuthors)->pluck('id')->all();
        // title_id fourni : titre existant, rattaché tel quel ; sinon recherche (libellé, VO, traducteurs, auteurs)
        $title = $this->lookup->findTitle($text, $authorIds);

        if (!$title) {
            $title = Title::create($this->titleAttributes($text, $book));
            $title->authors()->attach($authorIds);                  // author_title
        }

        return $title;
    }

    /**
     * titles : type, variant_type, is_genre, genre_stat et target_audience sont NOT NULL sans défaut.
     * Le genre et l'audience viennent du livre (un roman = un seul texte) ; à défaut : "inconnu".
     */
    private function titleAttributes(array $text, array $book): array
    {
        $attrs = [
            'name'            => BookLookup::norm($text['titre']),
            'type'            => $text['type_texte'] ?? null,
            'variant_type'    => TitleVariantType::PREMIER->value,   // première publication connue
            'copyright'       => BookLookup::norm($text['copyright'] ?? ''),
            'title_vo'        => BookLookup::norm($text['titre_vo'] ?? ''),
            'translators'     => BookLookup::translators($text),
            'is_genre'        => ($text['est_genre'] ?? null) ?: ($book['est_genre'] ?? null),
            'genre_stat'      => (($text['genre'] ?? null) ?: ($book['genre'] ?? null)) ?: GenreStat::INCONNU->value,
            'target_audience' => ($book['audience'] ?? null) ?: AudienceTarget::INCONNU->value,
            'target_age'      => $book['age'] ?? null,
            'is_novelization' => $text['novellisation'] ?? null,   // enum IsNovelization (défaut : non)
        ];

        return array_filter($attrs, fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Tous les cycles du texte (BookLookup::series), quel que soit leur niveau, chacun rattaché au titre
     * avec son numéro. Un cycle absent de la base est créé, après son parent s'il en a un (parent_id).
     * La hiérarchie des cycles déjà en base n'est jamais modifiée.
     *
     * @return Cycle[]
     */
    private function findOrCreateCycles(array $text, Title $title): array
    {
        $series = BookLookup::series($text);
        $parents = array_column($series, 'parent', 'name');
        $cycles = [];

        foreach ($series as $serie) {
            // cycle_id fourni : cycle existant ; sinon par nom, créé (après son parent) s'il manque
            $cycle = ($serie['id'] !== null ? $this->lookup->findCycle($serie) : null)
                ?? $this->findOrCreateCycle($serie['name'], $parents);

            // cycle_title : number (libellé du n°), order (valeur numérique pour le tri)
            $number = $serie['number'];
            $cycle->titles()->syncWithoutDetaching([
                $title->id => [
                    'number' => $number !== '' ? $number : null,
                    'order'  => self::numeric($number),
                ],
            ]);

            $cycles[] = $cycle;
        }

        return $cycles;
    }

    /**
     * Livre sans ISBN déjà en base (ou déjà importé plus haut dans ce run, y compris en dry-run).
     * Éditeur/collection non résolus : false, le livre sera ignoré à l'étape 1.
     */
    private function existsWithoutIsbn(array $book): bool
    {
        if (BookLookup::realIsbn($book['isbn'] ?? '') !== '') {
            return false;
        }

        $resolved = $this->lookup->resolve($book);

        return $resolved['missing'] === null
            && $this->lookup->publicationExistsWithoutIsbn($book, $resolved['publisher'], $resolved['collection']);
    }

    /** Signale (une fois par cycle) un cycle existant sans parent alors que le fichier lui en indique un. Aucune écriture. */
    private function noteCyclesMissingParent(array $book, int $n): void
    {
        foreach (BookLookup::texts($book) as $text) {
            foreach ($this->lookup->cyclesMissingParent($text) as $m) {
                $key = BookLookup::key($m['cycle']) . '|' . BookLookup::key($m['parent']);

                if (!isset($this->stats['cycles_missing_parent'][$key])) {
                    $this->stats['cycles_missing_parent'][$key] = $m + ['books' => []];
                    $this->ui->warn("Livre #{$n} : le cycle « {$m['cycle']} » (#{$m['id']}) existe en base sans parent ; le fichier indique « {$m['parent']} » (non modifié)");
                }
                $this->stats['cycles_missing_parent'][$key]['books'][] = $n;
            }
        }
    }

    /**
     * Cycle existant, sinon créé : son parent d'abord (récursivement), puis lui avec parent_id.
     * Un cycle créé comme parent est de type "cycle", les autres de type "série".
     *
     * @param array<string, string> $parents nom de cycle => nom de son parent ('' si aucun)
     * @param array<string, true> $visiting protection contre une hiérarchie circulaire
     */
    private function findOrCreateCycle(string $name, array $parents, bool $asParent = false, array $visiting = []): Cycle
    {
        if ($cycle = $this->lookup->cycle($name)) {
            return $cycle;
        }

        $parentName = $parents[$name] ?? '';
        $parent = null;

        if ($parentName !== '' && $parentName !== $name && !isset($visiting[$parentName])) {
            $parent = $this->findOrCreateCycle($parentName, $parents, true, $visiting + [$name => true]);
        }

        return Cycle::create(array_filter([
            'name'      => $name,
            'type'      => ($asParent ? CycleType::CYCLE : CycleType::SERIE)->value,
            'quality'   => QualityStatus::VIDE->value,
            'parent_id' => $parent?->id,
        ], fn ($v) => $v !== null));
    }

    private function createPublication(array $book, Publisher $publisher, ?Collection $collection, array $authors, array $titles): Publication
    {
        $attributes = $this->publicationAttributes($book, $publisher);

        // Réédition : first_edition_id = l'ouvrage d'origine (et non la publication précédente)
        if ($firstEditionId = $this->firstEditionId($book)) {
            $attributes['first_edition_id'] = $firstEditionId;
        }

        $publication = Publication::create($attributes);

        // Ouvrage d'origine d'un groupe d'éditions : mémorisé pour ses rééditions
        if (($book['rang_edition'] ?? null) === 0 && isset($book['groupe_edition'])) {
            $this->firstEditionByGroup[$book['groupe_edition']] = $publication->id;
        }

        // collection_publication : order (unsigned, NOT NULL) et number (libellé du n°) ; sans collection, rien
        if ($collection) {
            $number = BookLookup::norm($book['no_collection'] ?? '');
            $publication->collections()->attach($collection->id, [
                'order'  => (int) self::numeric($number),
                'number' => $number !== '' ? $number : null,
            ]);
        }

        // author_publication : role NOT NULL
        $publication->authors()->attach(
            collect(BookLookup::authors($book['auteurs']))
                ->mapWithKeys(fn ($a) => [$authors[$a['key']]->id => ['role' => $a['role']]])   // AuthorPublicationRole : author / editor
                ->all()
        );

        // table_of_content : level (0 = contenant / contenu principal, 1 = texte inclus, 2 = sous-niveau),
        // order (rang dans le sommaire, à partir de 1), start_page. Un attach() par ligne : un même titre
        // peut apparaître deux fois dans un sommaire.
        foreach (BookLookup::texts($book) as $i => $text) {
            $page = BookLookup::norm($text['page'] ?? '');
            $publication->titles()->attach($titles[$i]->id, [
                'level'      => (float) ($text['niveau'] ?? 0),
                'order'      => $i + 1,
                'start_page' => $page !== '' ? mb_substr($page, 0, 8) : null,
            ]);
        }

        // Retirages (table reprints) : date, achevé d'imprimer, vérification
        foreach ($book['retirages'] ?? [] as $reprint) {
            Reprint::create(array_filter([
                'publication_id'       => $publication->id,
                'approximate_parution' => $reprint['approximate_parution'] ?? null,
                'ai'                   => $reprint['ai'] ?? null,
                'is_verified'          => $this->bool($reprint['est_verifie'] ?? null) ?? false,   // NOT NULL
                'verified_by'          => $reprint['verifie_par'] ?? null,
            ], fn ($v) => $v !== null && $v !== ''));
        }

        return $publication;
    }

    /**
     * first_edition_id d'une réédition : l'ouvrage d'origine, importé plus haut dans ce run (même groupe),
     * sinon retrouvé en base par son ISBN. Null si l'origine est inconnue.
     */
    private function firstEditionId(array $book): ?int
    {
        if (!($book['est_reedition'] ?? false)) {
            return null;
        }

        $group = $book['groupe_edition'] ?? null;
        if ($group !== null && isset($this->firstEditionByGroup[$group])) {
            return $this->firstEditionByGroup[$group];
        }

        $isbn = BookLookup::realIsbn($book['edition_originale_isbn'] ?? '');

        return $isbn !== '' ? $this->lookup->firstPublicationIdByIsbn($isbn) : null;
    }

    /**
     * Correspondance JSON canonique => colonnes de publications (colonnes confirmées par la migration).
     * Les valeurs vides sont omises : le défaut SQL s'applique. Exceptions : is_verified, type et is_genre
     * sont NOT NULL sans défaut (is_verified est forcé à false ici ; type et is_genre sont exigés par BookValidator).
     */
    private function publicationAttributes(array $book, Publisher $publisher): array
    {
        $attrs = [
            'publisher_id' => $publisher->id,
            'publisher_name' => $this->lookup->publisherNameOverride($book),   // ancien nom d'éditeur (alias de sigle)
            'name'         => BookLookup::norm($book['titre']),
            'status'       => $book['status'] ?? null,
            'cycle'        => BookLookup::norm($book['serie'] ?? ''),
            'cyclenum'     => BookLookup::norm($book['num_serie'] ?? ''),
            'is_visible'   => $this->bool($book['est_visible'] ?? null),
            'isbn'         => BookLookup::storedIsbn($book['isbn'] ?? ''),   // ISBN, "-" (pas d'ISBN) ou "?" (inconnu)

            'cover'        => $book['illustrateur_couv'] ?? null,
            'illustrators' => $book['illustrateurs'] ?? null,
            'information'  => $book['information'] ?? null,
            'private'      => $book['private'] ?? null,

            // Images (publications.cover_* / withband_* / dustjacket_*, 64 caractères max)
            'cover_front'      => $book['img_couv'] ?? null,
            'cover_back'       => $book['img_back'] ?? null,
            'cover_spine'      => $book['img_dos'] ?? null,
            'withband_front'   => $book['img_bandeau'] ?? null,
            'dustjacket_front' => $book['img_jaquette'] ?? null,

            'is_hardcover'   => $this->bool($book['reliure'] ?? null),
            'has_dustjacket' => $this->bool($book['jaquette'] ?? null),
            'has_coverflaps' => $this->bool($book['rabat'] ?? null),

            'is_verified'  => $this->bool($book['est_verifie'] ?? null) ?? false,
            'verified_by'  => $book['verifie_par'] ?? null,

            'dl'           => $book['dl'] ?? null,
            'ai'           => $book['ai'] ?? null,
            'dimensions'   => $book['dimensions'] ?? null,
            'printer'      => $book['imprimeur'] ?? null,

            'approximate_pages'    => $book['approximate_pages'] ?? null,
            'approximate_parution' => $book['approximate_parution'] ?? null,

            'support'         => $book['support'] ?? null,
            'format'          => $book['format'] ?? null,
            'type'            => $book['type_ouvrage'] ?? null,
            'is_genre'        => $book['est_genre'] ?? null,
            'genre_stat'      => $book['genre'] ?? null,
            'target_audience' => $book['audience'] ?? null,
            'target_age'      => $book['age'] ?? null,
        ];

        return array_filter($attrs, fn ($v) => $v !== null && $v !== '');
    }

    /** null/'' => null (défaut SQL) ; sinon booléen */
    private function bool(mixed $value): ?bool
    {
        if (is_bool($value) || $value === null) {
            return $value;
        }

        $value = mb_strtolower(trim((string) $value));

        return $value === '' ? null : in_array($value, ['1', 'true', 'oui', 'yes', 'o'], true);
    }

    /** Première valeur numérique d'un libellé ("12", "T3", "2.5", "4 bis" => 12, 3, 2.5, 4) ; 0 si aucune */
    private static function numeric(string $label): float
    {
        return preg_match('/\d+(?:[.,]\d+)?/', $label, $m) ? (float) str_replace(',', '.', $m[0]) : 0.0;
    }
}