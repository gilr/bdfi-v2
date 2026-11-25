<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Publication;
use App\Enums\PublicationStatus;

/**
 * Liste des publications annoncées (dates échues ou non) avec actions rapides : changer la date de parution,
 * marquer "paru" ou "jamais paru". Le reste (collection, auteurs, contenu) se fait dans la fiche Filament.
 */
class ScheduledPublications extends Component
{
    /** 'expired' : date atteinte ou dépassée ; 'future' : date à venir */
    public string $mode = 'future';

    /** Dates en cours de saisie, par id de publication */
    public array $dates = [];

    public string $notice = '';
    public string $error = '';

    // Obligatoire pour interdire les bots pirateurs
    protected static $middlewares = [
        'auth.bdfiadmin',
        'admin.maintenance',
    ];

    /**
     * La date est-elle précise (pas "circa") et entièrement passée ? AAAA-00-00 : année écoulée ;
     * AAAA-MM-00 : mois écoulé ; AAAA-MM-JJ : jour atteint ; trimestre : non (pas assez précis).
     */
    public static function isPastAndPrecise(string $date): bool
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) {
            if ($m[2] === '00') {
                return (int) $m[1] < (int) date('Y');
            }
            if ($m[3] === '00') {
                return "{$m[1]}-{$m[2]}" < date('Y-m');
            }

            return $date <= date('Y-m-d');
        }

        return false;
    }

    private function find(int $id): ?Publication
    {
        abort_unless(auth()->user()?->hasMemberRole(), 403);
        $this->notice = $this->error = '';

        return Publication::where('status', PublicationStatus::ANNONCE->value)->find($id);
    }

    public function changeDate(int $id): void
    {
        $publication = $this->find($id);
        if (!$publication) {
            return;
        }

        $date = trim((string) ($this->dates[$id] ?? ''));
        if (!preg_match(PublicationSearchAndEdit::PARUTION_REGEX, $date)) {
            $this->error = "« {$publication->name} » : date invalide. Formats acceptés AAAA-MM-00, AAAA-00-00, AAAA-MM-JJ ou AAAA-Tn-00 (trimestre).";

            return;
        }
        if (str_contains($date, 'circa')) {
            $this->error = "« {$publication->name} » : une date approximative ne convient pas à un ouvrage à paraître (utilisez « Paru » si l'ouvrage est sorti).";

            return;
        }

        // Date précise et passée : l'ouvrage est paru, la date suffit
        if (self::isPastAndPrecise($date)) {
            $publication->update(['approximate_parution' => $date, 'status' => PublicationStatus::PUBLIE->value]);
            $this->notice = "« {$publication->name} » : date {$date} (passée), l'ouvrage est marqué paru.";
        } else {
            $publication->update(['approximate_parution' => $date]);
            $this->notice = "« {$publication->name} » : date de parution changée en {$date}.";
        }
        unset($this->dates[$id]);
    }

    public function markPublished(int $id): void
    {
        $publication = $this->find($id);
        if ($publication) {
            $publication->update(['status' => PublicationStatus::PUBLIE->value]);
            $this->notice = "« {$publication->name} » : marqué paru (date conservée : {$publication->approximate_parution}).";
        }
    }

    public function markAbandoned(int $id): void
    {
        $publication = $this->find($id);
        if ($publication) {
            $publication->update(['status' => PublicationStatus::ABANDONNE->value]);
            $this->notice = "« {$publication->name} » : marqué jamais paru.";
        }
    }

    public function render()
    {
        $today = date('Y-m-d');
        $query = Publication::with('publisher')->where('status', PublicationStatus::ANNONCE->value);
        $results = $this->mode === 'expired'
            ? $query->where('approximate_parution', '<=', $today)->orderBy('approximate_parution', 'desc')->get()
            : $query->where('approximate_parution', '>=', $today)->orderBy('approximate_parution', 'asc')->get();

        return view('livewire.scheduled-publications', compact('results'));
    }
}
