<?php

namespace App\Livewire;

use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Livewire\Concerns\SearchesPublications;
use App\Models\Publication;
use App\Enums\PublicationStatus;
use App\Enums\PublicationContent;
use App\Enums\PublicationSupport;
use App\Enums\PublicationFormat;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\AudienceTarget;

/**
 * Modification rapide d'une publication : données générales et indicatives. Ni éditeur, collection et
 * auteurs (fiche Filament), ni les données vérifiées livre en main (cf. PublicationSearchAndVerify).
 */
class PublicationSearchAndEdit extends Component
{
    use SearchesPublications;

    /** AAAA-MM-JJ (MM/JJ à 00 si inconnus), AAAA-Tn-00 (trimestre), ou AAAA-circa : comme à la création */
    public const PARUTION_REGEX = '/^(\d{4}-(\d{2}-\d{2}|T[1-4]-00)|-?\d{1,4}-circa)$/';

    public $selectedPublication = null;
    public $name = '';

    // Données du livre, hors données vérifiées
    public $isbn = '';
    public $no_isbn = false;
    public $cover = '';
    public $illustrators = '';
    public $information = '';
    public $private = '';
    public $type = PublicationContent::ROMAN->value;

    // Support physique
    public $support = PublicationSupport::PAPIER->value;
    public $format = PublicationFormat::INCONNU->value;
    public $is_hardcover = false;
    public $has_dustjacket = false;
    public $has_coverflaps = false;

    // Données indicatives
    public $approximate_pages = '';
    public $approximate_parution = '';
    public $approximate_price = '';
    public $is_genre = GenreAppartenance::OUI->value;
    public $genre_stat = GenreStat::INCONNU->value;
    public $target_audience = AudienceTarget::INCONNU->value;
    public $target_age = '';

    public string $success = '';

    // Obligatoire pour interdire les bots pirateurs
    protected static $middlewares = [
        'auth.bdfiadmin',
        'admin.maintenance',
    ];

    /** Lien direct depuis une liste : /admin/formulaires/modifier-publication?id=123 */
    public function mount(): void
    {
        if (is_numeric(request()->query('id'))) {
            $this->selectPublication((int) request()->query('id'));
        }
    }

    public function updated($property): void
    {
        $this->success = '';
    }

    public function selectPublication($publicationId)
    {
        $this->success = '';
        $this->resetErrorBag();

        $publication = Publication::with(['publisher', 'collections', 'authors'])->find($publicationId);
        if (!$publication) {
            return;
        }
        $this->selectedPublication = $publication;

        $this->name = $publication->name;
        $this->type = $publication->type->value;

        // "-" : pas d'ISBN (ouvrage ancien, fanzine...) ; "?" : ISBN inconnu
        $this->no_isbn = $publication->isbn === '-';
        $this->isbn = in_array($publication->isbn, ['-', '?'], true) ? '' : (string) $publication->isbn;

        // "?" : inconnu (convention des données existantes)
        $this->cover = $publication->cover === '?' ? '' : (string) $publication->cover;
        $this->illustrators = $publication->illustrators === '?' ? '' : (string) $publication->illustrators;
        $this->information = (string) $publication->information;
        $this->private = (string) $publication->private;

        $this->support = $publication->support->value;
        $this->format = $publication->format->value;
        $this->is_hardcover = (bool) $publication->is_hardcover;
        $this->has_dustjacket = (bool) $publication->has_dustjacket;
        $this->has_coverflaps = (bool) $publication->has_coverflaps;

        $this->approximate_pages = (string) $publication->approximate_pages;
        $this->approximate_parution = (string) $publication->approximate_parution;
        $this->approximate_price = (string) $publication->approximate_price;
        $this->is_genre = $publication->is_genre->value;
        $this->genre_stat = $publication->genre_stat->value;
        $this->target_audience = $publication->target_audience->value;
        $this->target_age = (string) $publication->target_age;
    }

    public function save()
    {
        // Modification réservée aux membres et plus (comme la création)
        abort_unless(auth()->user()?->hasMemberRole(), 403);
        $this->success = '';

        if (!$this->selectedPublication) {
            return;
        }

        $this->validate([
            'name'                 => ['required', 'max:128'],
            'type'                 => ['required', Rule::enum(PublicationContent::class)],
            'support'              => ['required', Rule::enum(PublicationSupport::class)],
            'format'               => ['required', Rule::enum(PublicationFormat::class)],
            'approximate_parution' => ['required', 'max:10', 'regex:' . self::PARUTION_REGEX],
            'approximate_pages'    => ['nullable', 'integer', 'min:0', 'max:99999'],
            'approximate_price'    => ['nullable', 'max:32'],
            'is_genre'             => ['required', Rule::enum(GenreAppartenance::class)],
            'genre_stat'           => ['required', Rule::enum(GenreStat::class)],
            'target_audience'      => ['required', Rule::enum(AudienceTarget::class)],
            'target_age'           => ['nullable', 'max:10'],
            'isbn'                 => ['nullable', 'max:18'],
            'no_isbn'              => ['boolean'],
            'is_hardcover'         => ['boolean'],
            'has_dustjacket'       => ['boolean'],
            'has_coverflaps'       => ['boolean'],
            'cover'                => ['nullable', 'max:256'],
            'illustrators'         => ['nullable', 'max:512'],
            'information'          => ['nullable', 'max:65535'],
            'private'              => ['nullable', 'max:65535'],
        ], [
            'approximate_parution.regex' => 'Date de publication : formats acceptés AAAA-MM-00, AAAA-00-00, AAAA-MM-JJ, AAAA-Tn-00 (trimestre) ou AAAA-circa.',
            'approximate_pages.integer'  => 'Pages approx : un nombre entier est attendu.',
        ], [
            'name' => 'titre', 'type' => 'type', 'support' => 'support', 'format' => 'format',
            'approximate_parution' => 'date de publication', 'approximate_pages' => 'pages approx',
            'approximate_price' => 'prix approx', 'is_genre' => 'genres référencés', 'genre_stat' => 'genre',
            'target_audience' => 'public', 'target_age' => 'âge cible', 'isbn' => 'ISBN',
            'cover' => 'couverture', 'illustrators' => 'illustrations intérieures',
            'information' => 'informations', 'private' => 'infos privées',
        ]);

        // Un ouvrage à paraître a une date précise, jamais approximative
        if ($this->selectedPublication->status === PublicationStatus::ANNONCE && str_contains($this->approximate_parution, 'circa')) {
            $this->addError('approximate_parution', 'Ouvrage à paraître : la date prévue doit être précise, pas approximative.');

            return;
        }

        $this->selectedPublication->update([
            'name'                 => trim($this->name),
            'type'                 => $this->type,
            'support'              => $this->support,
            'format'               => $this->format,
            'approximate_parution' => $this->approximate_parution,
            'approximate_pages'    => filled($this->approximate_pages) ? (int) $this->approximate_pages : null,
            'approximate_price'    => filled($this->approximate_price) ? trim($this->approximate_price) : '',
            'is_genre'             => $this->is_genre,
            'genre_stat'           => $this->genre_stat,
            'target_audience'      => $this->target_audience,
            'target_age'           => filled($this->target_age) ? trim($this->target_age) : '',
            // "-" : pas d'ISBN ; "?" : ISBN inconnu (champ laissé vide)
            'isbn'                 => $this->no_isbn ? '-' : (filled($this->isbn) ? trim($this->isbn) : '?'),
            'is_hardcover'         => (bool) $this->is_hardcover,
            'has_dustjacket'       => (bool) $this->has_dustjacket,
            'has_coverflaps'       => (bool) $this->has_coverflaps,
            'cover'                => filled($this->cover) ? trim($this->cover) : '?',
            'illustrators'         => filled($this->illustrators) ? trim($this->illustrators) : '?',
            'information'          => filled($this->information) ? $this->information : null,
            'private'              => filled($this->private) ? $this->private : null,
        ]);

        $this->success = 'Modifications enregistrées avec succès.';
    }

    public function resetSelection()
    {
        $this->reset([
            'search', 'publications', 'selectedPublication', 'name', 'isbn', 'no_isbn', 'cover', 'illustrators',
            'information', 'private', 'type', 'support', 'format', 'is_hardcover', 'has_dustjacket', 'has_coverflaps',
            'approximate_pages', 'approximate_parution', 'approximate_price', 'is_genre', 'genre_stat',
            'target_audience', 'target_age', 'success',
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.publication-search-and-edit', [
            'contents' => PublicationContent::cases(),
            'supports' => PublicationSupport::cases(),
            'formats' => PublicationFormat::cases(),
        ]);
    }
}
