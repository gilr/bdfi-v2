<?php

namespace App\Livewire;

use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Enums\AudienceTarget;
use App\Enums\GenreAppartenance;
use App\Enums\GenreStat;
use App\Enums\IsNovelization;
use App\Enums\TitleType;
use App\Models\Title;

/**
 * Modification et complément d'un texte (oeuvre, contenu). Ni auteurs ni séries (fiche Filament), ni variantes
 * (formulaire des variantes) : les textes ne sont pas créés ici, mais avec une publication ou un sommaire.
 * Le slug (adresse de la page) n'est pas modifié par un changement de titre.
 */
class TitleSearchAndEdit extends Component
{
    /** AAAA-MM-JJ (MM/JJ à 00 si inconnus) ou AAAA-Tn-00 (trimestre) ; année négative possible (-0400-00-00) */
    public const DATE_REGEX = '/^[\-012][\-0-9]{3}-(T[1-4]-00|\d{2}-\d{2})$/';

    public $search = '';
    public $titles = [];
    public $selectedTitle = null;

    public $name = '';
    public $type = TitleType::ROMAN->value;
    public $is_novelization = IsNovelization::NON->value;
    public $copyright = '';
    public $copyright_fr = '';
    public $title_vo = '';
    public $pub_vo = '';
    public $translators = '';
    public $is_genre = GenreAppartenance::OUI->value;
    public $genre_stat = GenreStat::INCONNU->value;
    public $target_audience = AudienceTarget::INCONNU->value;
    public $target_age = '';
    public $is_visible = true;
    public $is_serial = false;
    public $is_fullserial = false;
    public $serial_info = '';
    public $synopsis = '';
    public $information = '';
    public $private = '';

    public string $success = '';

    // Obligatoire pour interdire les bots pirateurs
    protected static $middlewares = [
        'auth.bdfiadmin',
        'admin.maintenance',
    ];

    /** Mêmes champs que le formulaire, pour le chargement et la remise à zéro */
    private const FIELDS = [
        'name', 'type', 'is_novelization', 'copyright', 'copyright_fr', 'title_vo', 'pub_vo', 'translators', 'is_genre',
        'genre_stat', 'target_audience', 'target_age', 'is_visible', 'is_serial', 'is_fullserial', 'serial_info',
        'synopsis', 'information', 'private',
    ];

    /** Lien direct depuis une fiche, un rapport... : /admin/formulaires/modifier-oeuvre?id=123 */
    public function mount(): void
    {
        if (is_numeric(request()->query('id'))) {
            $this->selectTitle((int) request()->query('id'));
        }
    }

    public function updated($property): void
    {
        $this->success = '';
    }

    /** Extrait du titre, ou "#123" (identifiant) */
    public function updatedSearch()
    {
        $text = trim($this->search);
        if (mb_strlen($text) < 2) {
            $this->titles = [];

            return;
        }

        $this->titles = Title::with('authors:id,name,first_name')
            ->where(function ($q) use ($text) {
                $q->where('name', 'like', "%{$text}%");
                if (preg_match('/^#(\d+)$/', $text, $m)) {
                    $q->orWhere('id', (int) $m[1]);
                }
            })
            ->orderBy('name')->orderBy('copyright_fr')
            ->limit(24)
            ->get();
    }

    public function selectTitle($titleId)
    {
        $this->success = '';
        $this->resetErrorBag();

        $title = Title::with(['authors', 'parent', 'publications' => fn ($q) => $q->limit(6)])->find($titleId);
        if (!$title) {
            return;
        }
        $this->selectedTitle = $title;

        $this->name = $title->name;
        $this->type = $title->type->value;
        $this->is_novelization = $title->is_novelization->value;
        $this->copyright = (string) $title->copyright;
        $this->copyright_fr = (string) $title->copyright_fr;
        $this->title_vo = (string) $title->title_vo;
        $this->pub_vo = (string) $title->pub_vo;
        $this->translators = (string) $title->translators;
        $this->is_genre = $title->is_genre->value;
        $this->genre_stat = $title->genre_stat->value;
        $this->target_audience = $title->target_audience->value;
        $this->target_age = (string) $title->target_age;
        $this->is_visible = (bool) $title->is_visible;
        $this->is_serial = (bool) $title->is_serial;
        $this->is_fullserial = (bool) $title->is_fullserial;
        $this->serial_info = (string) $title->serial_info;
        $this->synopsis = (string) $title->synopsis;
        $this->information = (string) $title->information;
        $this->private = (string) $title->private;
    }

    public function save()
    {
        // Modification réservée aux membres et plus
        abort_unless(auth()->user()?->hasMemberRole(), 403);
        $this->success = '';

        if (!$this->selectedTitle) {
            return;
        }

        $this->validate([
            'name'            => ['required', 'max:256'],
            'type'            => ['required', Rule::enum(TitleType::class)],
            'is_novelization' => ['required', Rule::enum(IsNovelization::class)],
            'copyright'       => ['nullable', 'max:10', 'regex:' . self::DATE_REGEX],
            'copyright_fr'    => ['nullable', 'max:10', 'regex:' . self::DATE_REGEX],
            'title_vo'        => ['nullable', 'max:512'],
            'pub_vo'          => ['nullable', 'max:256'],
            'translators'     => ['nullable', 'max:512'],
            'is_genre'        => ['required', Rule::enum(GenreAppartenance::class)],
            'genre_stat'      => ['required', Rule::enum(GenreStat::class)],
            'target_audience' => ['required', Rule::enum(AudienceTarget::class)],
            'target_age'      => ['nullable', 'max:10'],
            'is_visible'      => ['boolean'],
            'is_serial'       => ['boolean'],
            'is_fullserial'   => ['boolean'],
            'serial_info'     => ['nullable', 'max:512'],
            'synopsis'        => ['nullable', 'max:65535'],
            'information'     => ['nullable', 'max:65535'],
            'private'         => ['nullable', 'max:65535'],
        ], [
            'copyright.regex'    => 'Date de copyright : formats acceptés AAAA-MM-JJ (00 si inconnu) ou AAAA-Tn-00 (trimestre).',
            'copyright_fr.regex' => 'Première parution fr : formats acceptés AAAA-MM-JJ (00 si inconnu) ou AAAA-Tn-00 (trimestre).',
        ], [
            'name' => 'titre', 'type' => 'type', 'is_novelization' => 'novellisation', 'copyright' => 'copyright',
            'copyright_fr' => 'première parution fr', 'title_vo' => 'titre original', 'pub_vo' => 'publication d\'origine',
            'translators' => 'traducteurs', 'is_genre' => 'genres référencés', 'genre_stat' => 'genre',
            'target_audience' => 'public', 'target_age' => 'âge cible', 'serial_info' => 'infos feuilleton',
            'synopsis' => 'synopsis', 'information' => 'informations', 'private' => 'infos privées',
        ]);

        // Conventions des données existantes : '' pour les textes courts vides ; null pour date fr, résumé,
        // informations et infos feuilleton vides
        $this->selectedTitle->update([
            'name'            => trim($this->name),
            'type'            => $this->type,
            'is_novelization' => $this->is_novelization,
            'copyright'       => trim((string) $this->copyright),
            'copyright_fr'    => filled($this->copyright_fr) ? trim($this->copyright_fr) : null,
            'title_vo'        => trim((string) $this->title_vo),
            'pub_vo'          => trim((string) $this->pub_vo),
            'translators'     => trim((string) $this->translators),
            'is_genre'        => $this->is_genre,
            'genre_stat'      => $this->genre_stat,
            'target_audience' => $this->target_audience,
            'target_age'      => trim((string) $this->target_age),
            'is_visible'      => (bool) $this->is_visible,
            'is_serial'       => (bool) $this->is_serial,
            'is_fullserial'   => (bool) $this->is_fullserial,
            'serial_info'     => filled($this->serial_info) ? trim($this->serial_info) : null,
            'synopsis'        => filled($this->synopsis) ? $this->synopsis : null,
            'information'     => filled($this->information) ? $this->information : null,
            'private'         => (string) $this->private,
        ]);

        $this->success = 'Modifications enregistrées avec succès.';
    }

    public function resetSelection()
    {
        $this->reset(['search', 'titles', 'selectedTitle', 'success', ...self::FIELDS]);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.title-search-and-edit', [
            'types'          => TitleType::cases(),
            'novelizations'  => IsNovelization::cases(),
        ]);
    }
}
