<?php

namespace App\Livewire;

use Livewire\Component;
use App\Livewire\Concerns\SearchesPublications;
use App\Models\Publication;
use App\Enums\PublicationStatus;

/**
 * Vérification d'une publication avec l'ouvrage entre les mains : passage à "vérifié" et saisie des données
 * précises relevées sur le livre (DL, AI, imprimeur, pagination, dimensions...). Ni éditeur, collection et
 * auteurs, ni les données générales (cf. PublicationSearchAndEdit).
 */
class PublicationSearchAndVerify extends Component
{
    use SearchesPublications;

    /** AAAA-MM-JJ (MM/JJ à 00 si inconnus) ou AAAA-Tn-00 (trimestre) ; "Non indiq." existe dans les données */
    private const PRINT_DATE = '(\d{4}-(\d{2}-\d{2}|T[1-4]-00)|Non indiq\.)';

    public $selectedPublication = null;
    public $name = '';

    // Cycle et numérotation, tels qu'indiqués sur l'ouvrage
    public $cycle = '';
    public $cyclenum = '';

    // Les autres infos vérifiables visuellement
    public $is_verified = false;
    public $verified_by = '';
    public $dl = '';
    public $ai = '';
    public $edition = '';
    public $dimensions = '';
    public $thickness = '';
    public $printer = '';
    public $printed_price = '';
    public $pagination = '';
    public $pages_dpi = '';
    public $pages_dpu = '';

    public string $success = '';

    // Obligatoire pour interdire les bots pirateurs
    protected static $middlewares = [
        'auth.bdfiadmin',
        'admin.maintenance',
    ];

    /** On ne vérifie qu'un ouvrage paru, puisqu'on l'a entre les mains */
    protected function searchableQuery()
    {
        return Publication::with('publisher')->where('status', PublicationStatus::PUBLIE->value);
    }

    public function updated($property): void
    {
        $this->success = '';

        // Coché : le vérificateur est par défaut l'utilisateur courant, modifiable
        if ($property === 'is_verified' && $this->is_verified && trim((string) $this->verified_by) === '') {
            $this->verified_by = (string) auth()->user()?->name;
        }
    }

    public function selectPublication($publicationId)
    {
        $this->success = '';
        $this->resetErrorBag();

        $publication = $this->searchableQuery()->with(['collections', 'authors'])->find($publicationId);
        if (!$publication) {
            return;
        }
        $this->selectedPublication = $publication;

        $this->name = $publication->name;
        $this->cycle = (string) $publication->cycle;
        $this->cyclenum = (string) $publication->cyclenum;

        $this->is_verified = (bool) $publication->is_verified;
        $this->verified_by = (string) $publication->verified_by;
        $this->dl = (string) $publication->dl;
        $this->ai = (string) $publication->ai;
        $this->edition = (string) $publication->edition;
        $this->dimensions = (string) $publication->dimensions;
        $this->thickness = (string) $publication->thickness;
        $this->printer = (string) $publication->printer;
        $this->printed_price = (string) $publication->printed_price;
        $this->pagination = (string) $publication->pagination;
        $this->pages_dpi = (string) $publication->pages_dpi;
        $this->pages_dpu = (string) $publication->pages_dpu;
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
            'cycle'         => ['nullable', 'max:128'],
            'cyclenum'      => ['nullable', 'max:10'],
            'is_verified'   => ['boolean'],
            'verified_by'   => ['nullable', 'max:256'],
            'dl'            => ['nullable', 'max:10', 'regex:/^(' . substr(self::PRINT_DATE, 1, -1) . '|A parution)$/'],
            'ai'            => ['nullable', 'max:10', 'regex:/^' . self::PRINT_DATE . '$/'],
            'edition'       => ['nullable', 'max:64'],
            'dimensions'    => ['nullable', 'max:10', 'regex:/^\d{2,3} x \d{2,3}$/'],
            'thickness'     => ['nullable', 'max:4', 'regex:/^\d{1,3}([.,]\d)?$/'],
            'printer'       => ['nullable', 'max:128'],
            'printed_price' => ['nullable', 'max:32'],
            'pagination'    => ['nullable', 'max:32'],
            'pages_dpi'     => ['nullable', 'integer', 'min:0', 'max:99999'],
            'pages_dpu'     => ['nullable', 'integer', 'min:0', 'max:99999'],
        ], [
            'dl.regex'         => 'DL : formats acceptés AAAA-MM-JJ (00 si inconnu), AAAA-Tn-00 (trimestre) ou « A parution ».',
            'ai.regex'         => 'AI : formats acceptés AAAA-MM-JJ (00 si inconnu) ou AAAA-Tn-00 (trimestre).',
            'dimensions.regex' => 'Dimensions : format « largeur x hauteur » en millimètres, par exemple « 110 x 178 ».',
            'thickness.regex'  => 'Épaisseur : en millimètres, sans unité (par exemple 18).',
            'pages_dpi.integer' => 'Dernière page numérotée : un nombre entier est attendu.',
            'pages_dpu.integer' => 'Dernière page utile : un nombre entier est attendu.',
        ], [
            'cycle' => 'série', 'cyclenum' => 'n° dans la série', 'verified_by' => 'vérificateur',
            'dl' => 'DL', 'ai' => 'AI', 'edition' => 'édition', 'dimensions' => 'dimensions',
            'thickness' => 'épaisseur', 'printer' => 'imprimeur', 'printed_price' => 'prix ou code prix',
            'pagination' => 'pagination', 'pages_dpi' => 'dernière page numérotée', 'pages_dpu' => 'dernière page utile',
        ]);

        // Vérifié sans vérificateur indiqué : l'utilisateur courant
        $verifiedBy = trim((string) $this->verified_by);
        if ($this->is_verified && $verifiedBy === '') {
            $verifiedBy = (string) auth()->user()?->name;
            $this->verified_by = $verifiedBy;
        }

        $this->selectedPublication->update([
            'cycle'         => trim((string) $this->cycle),
            'cyclenum'      => trim((string) $this->cyclenum),
            'is_verified'   => (bool) $this->is_verified,
            'verified_by'   => $verifiedBy,
            'dl'            => trim((string) $this->dl),
            'ai'            => trim((string) $this->ai),
            'edition'       => trim((string) $this->edition),
            'dimensions'    => trim((string) $this->dimensions),
            'thickness'     => str_replace(',', '.', trim((string) $this->thickness)),
            'printer'       => trim((string) $this->printer),
            'printed_price' => trim((string) $this->printed_price),
            'pagination'    => trim((string) $this->pagination),
            'pages_dpi'     => trim((string) $this->pages_dpi),
            'pages_dpu'     => trim((string) $this->pages_dpu),
        ]);

        $this->success = 'Modifications enregistrées avec succès.';
    }

    public function resetSelection()
    {
        $this->reset([
            'search', 'publications', 'selectedPublication', 'name', 'cycle', 'cyclenum', 'is_verified', 'verified_by',
            'dl', 'ai', 'edition', 'dimensions', 'thickness', 'printer', 'printed_price', 'pagination',
            'pages_dpi', 'pages_dpu', 'success',
        ]);
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.publication-search-and-verify');
    }
}
