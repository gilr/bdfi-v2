<?php

namespace App\Livewire;

use App\Bdfi\Import\BookLookup;
use App\Bdfi\MainContentFinder;
use App\Enums\AudienceTarget;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\IsNovelization;
use App\Enums\TitleType;
use App\Enums\TitleVariantType;
use App\Livewire\Concerns\SearchesPublications;
use App\Models\Author;
use App\Models\Publication;
use App\Models\TableOfContent;
use App\Models\Title;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Sommaire d'une publication : choix de la publication, rappel de ses données (non modifiables), puis table des
 * matières (table_of_content) avec en racine le contenu principal (niveau 0, toujours présent), et dessous les autres
 * textes, indentés selon leur niveau.
 * Actions : modifier l'ordre, le niveau et la pagination des rattachements ; retirer un texte du sommaire (rompt le
 * rattachement, le texte reste en base) ; rattacher un texte existant ; créer un texte (toutes les données de la table
 * titles) et le rattacher. Les textes créés ici sont des textes "premiers" : les variantes se gèrent dans leur formulaire.
 */
class PublicationSummaryEditor extends Component
{
    use SearchesPublications;

    /** Niveaux admis (table_of_content.level decimal(2,1)) : 0 = contenu principal, 0.5 / 1.5 = sections, 1 = texte, 2 = sous-niveau */
    public const LEVELS = ['0', '0.5', '1', '1.5', '2', '2.5', '3'];

    public ?int $publicationId = null;

    /** @var array<int, array{order: string, level: string, start_page: string, end_page: string}> rattachements, par id de ligne de sommaire */
    public array $rows = [];

    // Rattacher un texte existant
    public string $addSearch = '';
    public array $addResults = [];

    // Créer un texte
    public bool $showNew = false;
    public bool $confirmDuplicate = false;
    public array $new = [];
    /** @var array<int, string> auteurs du nouveau texte : id => libellé */
    public array $newAuthors = [];
    public string $authorSearch = '';
    public array $authorResults = [];
    public string $newAuthorLast = '';
    public string $newAuthorFirst = '';
    /** @var int[] textes existants identiques au texte à créer (doublon possible) */
    public array $duplicates = [];

    public string $notice = '';
    public string $error = '';

    // Obligatoire pour interdire les bots pirateurs
    protected static $middlewares = [
        'auth.bdfiadmin',
        'admin.maintenance',
    ];

    /** Lien direct depuis une page : /admin/formulaires/modifier-sommaire?id=123 */
    public function mount(): void
    {
        if (is_numeric(request()->query('id'))) {
            $this->selectPublication((int) request()->query('id'));
        }
    }

    // ------------------------------------------------------------------ Données

    private function publication(): ?Publication
    {
        return $this->publicationId
            ? Publication::with(['publisher', 'collections', 'authors'])->find($this->publicationId)
            : null;
    }

    /** @return Collection<int, TableOfContent> lignes du sommaire dans l'ordre enregistré */
    private function toc(): Collection
    {
        return TableOfContent::where('publication_id', $this->publicationId)
            ->with('title.authors')->orderBy('order')->orderBy('id')->get();
    }

    /** Contenu principal : la première ligne de niveau 0 */
    private function mainOf(Collection $toc): ?TableOfContent
    {
        return $toc->first(fn ($t) => (float) $t->level === 0.0);
    }

    private static function levelKey($level): string
    {
        return (string) (float) $level;   // "1.0" => "1", "0.5" => "0.5"
    }

    private function loadRows(): void
    {
        $this->rows = $this->toc()->mapWithKeys(fn ($t) => [$t->id => [
            'order'      => (string) $t->order,
            'level'      => self::levelKey($t->level),
            'start_page' => (string) $t->start_page,
            'end_page'   => (string) $t->end_page,
        ]])->all();
    }

    private function guard(): bool
    {
        // Modification réservée aux membres et plus
        abort_unless(auth()->user()?->hasMemberRole(), 403);
        $this->notice = $this->error = '';

        return $this->publicationId !== null && $this->publication() !== null;
    }

    // ------------------------------------------------------------------ Choix de la publication

    public function selectPublication($publicationId): void
    {
        $publication = Publication::find($publicationId);
        if (!$publication) {
            return;
        }

        $this->resetErrorBag();
        $this->reset('notice', 'error', 'addSearch', 'addResults');
        $this->resetNew();
        $this->publicationId = $publication->id;
        $this->loadRows();
    }

    public function resetSelection(): void
    {
        $this->reset(['search', 'publications', 'publicationId', 'rows', 'addSearch', 'addResults', 'notice', 'error']);
        $this->resetNew();
        $this->resetErrorBag();
    }

    // ------------------------------------------------------------------ Rattachements existants

    /** Enregistre ordre, niveau et pagination ; le contenu principal reste en racine (niveau 0, premier) */
    public function saveRows(): void
    {
        if (!$this->guard()) {
            return;
        }

        $this->validate([
            'rows.*.order'      => ['required', 'integer', 'min:1', 'max:9999'],
            'rows.*.level'      => ['required', Rule::in(self::LEVELS)],
            'rows.*.start_page' => ['nullable', 'max:8'],
            'rows.*.end_page'   => ['nullable', 'max:8'],
        ], [], [
            'rows.*.order' => 'ordre', 'rows.*.level' => 'niveau', 'rows.*.start_page' => 'page de début', 'rows.*.end_page' => 'page de fin',
        ]);

        $toc = $this->toc();
        $main = $this->mainOf($toc);

        foreach ($toc as $t) {
            $row = $this->rows[$t->id] ?? null;
            if ($row && $main?->id !== $t->id && (float) $row['level'] === 0.0) {
                $this->addError("rows.{$t->id}.level", 'Seul le contenu principal est au niveau 0 : choisissez un niveau supérieur.');
            }
        }
        if ($this->getErrorBag()->isNotEmpty()) {
            return;
        }

        DB::transaction(function () use ($toc, $main) {
            foreach ($toc as $t) {
                $row = $this->rows[$t->id] ?? null;
                if (!$row) {
                    continue;
                }
                $isMain = $main?->id === $t->id;
                $values = [
                    'order'      => $isMain ? 1 : (int) $row['order'],
                    'level'      => $isMain ? 0 : (float) $row['level'],
                    'start_page' => filled($row['start_page']) ? trim($row['start_page']) : null,
                    'end_page'   => filled($row['end_page']) ? trim($row['end_page']) : null,
                ];
                if ($t->order != $values['order'] || (float) $t->level !== (float) $values['level']
                    || $t->start_page !== $values['start_page'] || $t->end_page !== $values['end_page']) {
                    TableOfContent::whereKey($t->id)->first()->update($values);
                }
            }
            $this->renumber();
        });

        $this->loadRows();
        $this->notice = 'Sommaire enregistré.';
    }

    /** Ordres consécutifs à partir de 1, le contenu principal en premier */
    private function renumber(): void
    {
        $toc = $this->toc();
        $main = $this->mainOf($toc);
        $sorted = $main ? $toc->reject(fn ($t) => $t->id === $main->id)->prepend($main) : $toc;

        foreach ($sorted->values() as $i => $t) {
            if ((int) $t->order !== $i + 1) {
                TableOfContent::whereKey($t->id)->first()->update(['order' => $i + 1]);
            }
        }
    }

    /** Retire un texte du sommaire (le texte lui-même reste en base) ; le contenu principal ne se retire pas */
    public function removeRow($tocId): void
    {
        if (!$this->guard()) {
            return;
        }

        $toc = $this->toc();
        $row = $toc->firstWhere('id', (int) $tocId);
        if (!$row) {
            return;
        }
        if ($this->mainOf($toc)?->id === $row->id) {
            $this->error = 'Le contenu principal ne peut pas être retiré du sommaire.';

            return;
        }

        DB::transaction(function () use ($row) {
            TableOfContent::where('id', $row->id)->delete();
            $this->renumber();
        });

        $this->loadRows();
        $this->notice = "« {$row->title->name} » retiré du sommaire (le texte reste en base).";
    }

    // ------------------------------------------------------------------ Rattacher un texte existant

    /** Extrait du titre, ou "#identifiant" ; hors textes déjà au sommaire de cette publication */
    public function updatedAddSearch(): void
    {
        $text = trim($this->addSearch);
        if (mb_strlen($text) < 2) {
            $this->addResults = [];

            return;
        }

        $attached = $this->toc()->pluck('title_id')->all();

        $this->addResults = Title::with('authors:id,name,first_name')
            ->where(function ($q) use ($text) {
                $q->where('name', 'like', "%{$text}%");
                if (preg_match('/^#(\d+)$/', $text, $m)) {
                    $q->orWhere('id', (int) $m[1]);
                }
            })
            ->whereNotIn('id', $attached)
            ->orderBy('name')->orderBy('copyright_fr')->limit(15)->get()
            ->map(fn ($t) => $this->titleCard($t))->all();
    }

    private function titleCard(Title $t): array
    {
        return [
            'id'           => $t->id,
            'name'         => $t->name,
            'slug'         => $t->slug,
            'type'         => $t->type?->getLabel(),
            'variant'      => (string) $t->variant_type,
            'authors'      => $t->authors->map(fn ($a) => trim($a->first_name . ' ' . $a->name))->implode(', '),
            'copyright_fr' => $t->copyright_fr,
            'title_vo'     => $t->title_vo,
            'translators'  => $t->translators,
        ];
    }

    /** Rattache un texte existant, en fin de sommaire au niveau 1 */
    public function attach($titleId): void
    {
        if (!$this->guard()) {
            return;
        }

        $toc = $this->toc();
        $title = Title::find($titleId);
        if (!$title) {
            return;
        }
        if ($this->mainOf($toc) === null) {
            $this->error = "Le contenu principal doit d'abord être rattaché ou créé.";

            return;
        }
        if ($toc->contains('title_id', $title->id)) {
            $this->error = "« {$title->name} » est déjà dans ce sommaire.";

            return;
        }

        TableOfContent::create([
            'publication_id' => $this->publicationId, 'title_id' => $title->id, 'level' => 1,
            'order' => (int) $toc->max('order') + 1,
        ]);

        $this->reset('addSearch', 'addResults', 'duplicates');
        $this->loadRows();
        $this->notice = "« {$title->name} » ajouté au sommaire : ajustez si besoin son ordre, son niveau et ses pages, puis enregistrez.";
    }

    /** Rattache comme contenu principal un texte existant, de même titre et mêmes auteurs que la publication */
    public function attachMain($titleId): void
    {
        if (!$this->guard()) {
            return;
        }

        $publication = $this->publication();
        $title = Title::find($titleId);
        $authorIds = $publication->authors->pluck('id')->all();

        if ($this->mainOf($this->toc()) !== null || !$title
            || !MainContentFinder::isIdentical($title, $publication->name, $authorIds, $publication->type->value)) {
            $this->error = "Ce texte n'a pas le même titre, les mêmes auteurs et un type compatible avec la publication.";

            return;
        }

        DB::transaction(function () use ($title) {
            TableOfContent::create(['publication_id' => $this->publicationId, 'title_id' => $title->id, 'level' => 0, 'order' => 1]);
            $this->renumber();
        });

        $this->reset('duplicates');
        $this->loadRows();
        $this->notice = "« {$title->name} » rattaché comme contenu principal.";
    }

    // ------------------------------------------------------------------ Créer un texte

    private function resetNew(): void
    {
        $this->reset(['showNew', 'confirmDuplicate', 'new', 'newAuthors', 'authorSearch', 'authorResults', 'newAuthorLast',
            'newAuthorFirst', 'duplicates']);
    }

    /** Ouvre le formulaire de création, prérempli d'après la publication (et ses auteurs pour le contenu principal) */
    public function openNew(): void
    {
        if (!$this->guard()) {
            return;
        }

        $publication = $this->publication();
        $isMain = $this->mainOf($this->toc()) === null;
        $parution = (string) $publication->approximate_parution;

        $this->resetNew();
        $this->showNew = true;
        $this->new = [
            'name'            => $isMain ? $publication->name : '',
            'type'            => $isMain ? ($publication->type->mainContentTypes()[0]->value) : '',
            'is_novelization' => IsNovelization::NON->value,
            'copyright'       => '',
            'copyright_fr'    => preg_match('/^\d{4}-(\d{2}-\d{2}|T[1-4]-00)$/', $parution) ? $parution : '',
            'title_vo'        => '',
            'pub_vo'          => '',
            'translators'     => '',
            'is_genre'        => $publication->is_genre->value,
            'genre_stat'      => $publication->genre_stat->value,
            'target_audience' => $publication->target_audience->value,
            'target_age'      => '',
            'is_visible'      => true,
            'is_serial'       => false,
            'is_fullserial'   => false,
            'serial_info'     => '',
            'synopsis'        => '',
            'information'     => '',
            'private'         => '',
            'level'           => $isMain ? '0' : '1',
            'start_page'      => '',
            'end_page'        => '',
        ];

        // Auteurs de la publication par défaut (obligatoirement pour le contenu principal) ; modifiables sinon
        $this->useBookAuthors();
    }

    public function closeNew(): void
    {
        $this->resetNew();
        $this->resetErrorBag();
    }

    public function useBookAuthors(): void
    {
        $publication = $this->publication();
        $this->newAuthors = $publication
            ? $publication->authors->mapWithKeys(fn ($a) => [$a->id => trim($a->first_name . ' ' . $a->name)])->all()
            : [];
    }

    public function updatedAuthorSearch(): void
    {
        $words = preg_split('/\s+/u', trim($this->authorSearch), -1, PREG_SPLIT_NO_EMPTY);
        if (mb_strlen(trim($this->authorSearch)) < 2) {
            $this->authorResults = [];

            return;
        }

        $this->authorResults = Author::where(function ($q) use ($words) {
                foreach ($words as $word) {
                    $q->where(fn ($q) => $q->where('name', 'like', "%{$word}%")->orWhere('first_name', 'like', "%{$word}%"));
                }
            })
            ->orderBy('name')->limit(8)->get(['id', 'name', 'first_name'])
            ->map(fn ($a) => ['id' => $a->id, 'label' => trim($a->first_name . ' ' . $a->name)])->all();
    }

    public function addAuthor($authorId): void
    {
        $author = Author::find($authorId);
        if ($author) {
            $this->newAuthors[$author->id] = trim($author->first_name . ' ' . $author->name);
        }
        $this->reset('authorSearch', 'authorResults');
    }

    public function removeAuthor($authorId): void
    {
        unset($this->newAuthors[$authorId]);
    }

    /** Auteur absent de la base : créé s'il n'existe pas déjà sous ce nom (sinon, à choisir dans la recherche) */
    public function createAuthor(): void
    {
        abort_unless(auth()->user()?->hasMemberRole(), 403);
        $this->error = '';

        $last = BookLookup::norm($this->newAuthorLast);
        $first = BookLookup::norm($this->newAuthorFirst);
        if ($last === '' || mb_strlen($last) > 32 || mb_strlen($first) > 32) {
            $this->error = "Nouvel auteur : le nom est obligatoire ; nom et prénom : 32 caractères au maximum.";

            return;
        }

        $existing = Author::where('name', $last)
            ->where(fn ($q) => $first === '' ? $q->whereNull('first_name')->orWhere('first_name', '') : $q->where('first_name', $first))
            ->first();
        if ($existing) {
            $this->error = "Un auteur « " . trim("{$first} {$last}") . " » existe déjà (#{$existing->id}) : sélectionnez-le avec la recherche.";

            return;
        }

        $author = Author::create(['name' => $last, 'first_name' => $first !== '' ? $first : null]);
        $this->newAuthors[$author->id] = trim($first . ' ' . $last);
        $this->reset('newAuthorLast', 'newAuthorFirst');
    }

    /** Types de texte du nouveau texte : ceux du type de publication pour le contenu principal, tous sinon */
    private function allowedTypes(bool $isMain, Publication $publication): array
    {
        return $isMain ? $publication->type->mainContentTypes() : TitleType::cases();
    }

    /** Crée le texte (avec ses auteurs) et le rattache ; un texte identique déjà en base est d'abord signalé */
    public function createText(): void
    {
        if (!$this->guard()) {
            return;
        }

        $publication = $this->publication();
        $toc = $this->toc();
        $isMain = $this->mainOf($toc) === null;
        $allowed = array_map(fn ($t) => $t->value, $this->allowedTypes($isMain, $publication));

        $this->validate([
            'new.name'            => ['required', 'max:256'],
            'new.type'            => ['required', Rule::in($allowed)],
            'new.is_novelization' => ['required', Rule::enum(IsNovelization::class)],
            'new.copyright'       => ['nullable', 'max:10', 'regex:' . TitleSearchAndEdit::DATE_REGEX],
            'new.copyright_fr'    => ['nullable', 'max:10', 'regex:' . TitleSearchAndEdit::DATE_REGEX],
            'new.title_vo'        => ['nullable', 'max:512'],
            'new.pub_vo'          => ['nullable', 'max:256'],
            'new.translators'     => ['nullable', 'max:512'],
            'new.is_genre'        => ['required', Rule::enum(GenreAppartenance::class)],
            'new.genre_stat'      => ['required', Rule::enum(GenreStat::class)],
            'new.target_audience' => ['required', Rule::enum(AudienceTarget::class)],
            'new.target_age'      => ['nullable', 'max:10'],
            'new.serial_info'     => ['nullable', 'max:512'],
            'new.synopsis'        => ['nullable', 'max:65535'],
            'new.information'     => ['nullable', 'max:65535'],
            'new.private'         => ['nullable', 'max:65535'],
            'new.level'           => ['required', Rule::in(self::LEVELS)],
            'new.start_page'      => ['nullable', 'max:8'],
            'new.end_page'        => ['nullable', 'max:8'],
        ], [
            'new.type.in'          => $isMain ? 'Type de texte non compatible avec le type de la publication.' : 'Type de texte invalide.',
            'new.copyright.regex'    => 'Date de copyright : formats acceptés AAAA-MM-JJ (00 si inconnu) ou AAAA-Tn-00 (trimestre).',
            'new.copyright_fr.regex' => 'Première parution fr : formats acceptés AAAA-MM-JJ (00 si inconnu) ou AAAA-Tn-00 (trimestre).',
        ], [
            'new.name' => 'titre', 'new.type' => 'type', 'new.copyright' => 'copyright', 'new.copyright_fr' => 'première parution fr',
            'new.title_vo' => 'titre original', 'new.pub_vo' => "publication d'origine", 'new.translators' => 'traducteurs',
            'new.level' => 'niveau', 'new.start_page' => 'page de début', 'new.end_page' => 'page de fin',
        ]);

        // Contenu principal : titre et auteurs de la publication, niveau 0 ; les autres textes ne peuvent pas être au niveau 0
        $name = $isMain ? $publication->name : BookLookup::norm($this->new['name']);
        $authorIds = $isMain ? $publication->authors->pluck('id')->all() : array_keys($this->newAuthors);
        if (!$isMain && (float) $this->new['level'] === 0.0) {
            $this->addError('new.level', 'Seul le contenu principal est au niveau 0 : choisissez un niveau supérieur.');

            return;
        }

        // Un texte identique existe peut-être déjà : à rattacher plutôt qu'à recréer (sauf confirmation)
        if (!$this->confirmDuplicate) {
            $attached = $toc->pluck('title_id')->all();
            $found = MainContentFinder::candidates($name, $authorIds, $isMain ? $publication->type->value : null)
                ->reject(fn ($t) => in_array($t->id, $attached, true));
            if ($found->isNotEmpty()) {
                $this->duplicates = $found->pluck('id')->all();
                $this->error = 'Des textes existants ont le même titre et les mêmes auteurs : rattachez le bon texte, ou confirmez la création d\'un nouveau texte.';

                return;
            }
        }

        $title = DB::transaction(function () use ($name, $authorIds, $isMain, $toc) {
            $n = $this->new;
            $title = Title::create([
                'name'            => $name,
                'type'            => $n['type'],
                'variant_type'    => TitleVariantType::PREMIER->value,
                'is_novelization' => $n['is_novelization'],
                'copyright'       => trim((string) $n['copyright']),
                'copyright_fr'    => filled($n['copyright_fr']) ? trim($n['copyright_fr']) : null,
                'title_vo'        => trim((string) $n['title_vo']),
                'pub_vo'          => trim((string) $n['pub_vo']),
                'translators'     => trim((string) $n['translators']),
                'is_genre'        => $n['is_genre'],
                'genre_stat'      => $n['genre_stat'],
                'target_audience' => $n['target_audience'],
                'target_age'      => trim((string) $n['target_age']),
                'is_visible'      => (bool) $n['is_visible'],
                'is_serial'       => (bool) $n['is_serial'],
                'is_fullserial'   => (bool) $n['is_fullserial'],
                'serial_info'     => filled($n['serial_info']) ? trim($n['serial_info']) : null,
                'synopsis'        => filled($n['synopsis']) ? $n['synopsis'] : null,
                'information'     => filled($n['information']) ? $n['information'] : null,
                'private'         => (string) $n['private'],
            ]);
            if ($authorIds) {
                $title->authors()->attach($authorIds);
            }
            TableOfContent::create([
                'publication_id' => $this->publicationId, 'title_id' => $title->id,
                'level'          => $isMain ? 0 : (float) $n['level'],
                'order'          => $isMain ? 1 : (int) $toc->max('order') + 1,
                'start_page'     => filled($n['start_page']) ? trim($n['start_page']) : null,
                'end_page'       => filled($n['end_page']) ? trim($n['end_page']) : null,
            ]);
            $this->renumber();

            return $title;
        });

        $this->resetNew();
        $this->loadRows();
        $this->notice = "Texte « {$title->name} » créé et ajouté au sommaire" . ($isMain ? ' comme contenu principal.' : '.');
    }

    /** Confirme la création malgré les textes identiques signalés */
    public function createAnyway(): void
    {
        $this->confirmDuplicate = true;
        $this->createText();
    }

    // ------------------------------------------------------------------ Affichage

    public function render()
    {
        $publication = $this->publication();
        $toc = $publication ? $this->toc() : collect();
        $main = $this->mainOf($toc);

        // Sans contenu principal : textes existants de même titre et mêmes auteurs, de type compatible
        $mainCandidates = collect();
        if ($publication && !$main) {
            $mainCandidates = MainContentFinder::candidates($publication->name, $publication->authors->pluck('id')->all(), $publication->type->value);
        }

        return view('livewire.publication-summary-editor', [
            'publication'    => $publication,
            'toc'            => $toc,
            'main'           => $main,
            'mainCandidates' => $mainCandidates->map(fn ($t) => $this->titleCard($t))->all(),
            'duplicateCards' => $this->duplicates
                ? Title::with('authors:id,name,first_name')->whereIn('id', $this->duplicates)->get()->map(fn ($t) => $this->titleCard($t))->all()
                : [],
            'levels'         => self::LEVELS,
            'newTypes'       => $publication ? $this->allowedTypes($main === null, $publication) : [],
            'novelizations'  => IsNovelization::cases(),
        ]);
    }
}
