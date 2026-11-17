<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Enums\EventPeriodicity;
use App\Enums\EventType;
use App\Models\Event;
use App\Models\EventOccurrence;

/**
 * Ajout d'une occurrence d'évènement (salon, festival...), avec création de l'évènement lui-même s'il
 * n'est pas encore référencé. Point d'entrée unique : recherche par extrait du nom, puis
 *  - choix d'un évènement existant : liste de ses occurrences, saisie de la nouvelle (pré-remplie
 *    d'après la dernière, décalée d'un an ou de deux selon la périodicité) ;
 *  - "créer ce nouvel évènement" : saisie de l'évènement et de sa première occurrence.
 *
 * Années de l'évènement : year_start reprend l'année de la première occurrence (et est écrasé si une
 * occurrence antérieure est ajoutée plus tard) ; year_end est vidé si une occurrence plus récente est ajoutée.
 */
class EventOccurrenceAdd extends Component
{
    public string $search = '';

    // null : recherche ; 'new' : nouvel évènement ; 'existing' : évènement $eventId
    public ?string $mode = null;
    public ?int $eventId = null;

    // Champs de l'évènement (mode 'new')
    public string $name = '';
    public string $type = '';
    public string $periodicity = '';
    public string $eventPlace = '';
    public string $url = '';
    public string $eventInformation = '';
    public bool $isFullScope = false;
    public string $yearStart = '';
    public string $yearEnd = '';

    // Champs de l'occurrence
    public string $label = '';
    public string $startDate = '';
    public string $endDate = '';
    public string $place = '';
    public string $information = '';
    public bool $isConfirmed = false;

    public string $notice = '';
    public string $error = '';

    // Obligatoire pour interdire les bots pirateurs
    protected static $middlewares = [
        'auth.bdfiadmin',
        'admin.maintenance',
    ];

    public function createNew(): void
    {
        $this->resetForm();
        $this->mode = 'new';
        $this->name = trim($this->search);
    }

    public function selectEvent(int $id): void
    {
        $event = Event::with('occurrences')->find($id);
        if (!$event) {
            $this->error = "Évènement #{$id} introuvable.";

            return;
        }
        $this->resetForm();
        $this->mode = 'existing';
        $this->eventId = $event->id;

        // Pré-remplissage d'après la dernière occurrence : mêmes jours de la semaine, 52 semaines plus tard
        // (104 pour un biennal), même lieu. Les dates restent à vérifier.
        $last = $event->occurrences->sortByDesc('start_date')->first();
        if ($last) {
            $weeks = $event->periodicity === EventPeriodicity::BIENNAL ? 104 : 52;
            $this->startDate = $last->start_date->copy()->addWeeks($weeks)->format('Y-m-d');
            $this->endDate = $last->end_date->copy()->addWeeks($weeks)->format('Y-m-d');
            $this->place = (string) $last->place;
        }
    }

    public function back(): void
    {
        $this->resetForm();
    }

    public function updatedStartDate(): void
    {
        // Date de fin proposée égale à la date de début si vide ou antérieure
        if ($this->startDate !== '' && ($this->endDate === '' || $this->endDate < $this->startDate)) {
            $this->endDate = $this->startDate;
        }
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasMemberRole(), 403);
        $this->notice = '';
        $this->error = '';
        if (!in_array($this->mode, ['new', 'existing'], true)) {
            return;
        }

        $rules = [
            'label' => 'nullable|string|max:128',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'place' => 'nullable|string|max:128',
            'information' => 'nullable|string|max:65535',
        ];
        if ($this->mode === 'new') {
            $rules += [
                'name' => 'required|string|max:128',
                'type' => ['required', Rule::enum(EventType::class)],
                'periodicity' => ['nullable', Rule::enum(EventPeriodicity::class)],
                'eventPlace' => 'nullable|string|max:64',
                'url' => 'nullable|string|max:256',
                'eventInformation' => 'nullable|string|max:65535',
                'yearStart' => 'nullable|digits:4',
                'yearEnd' => 'nullable|digits:4',
            ];
        }
        $this->validate($rules, [
            'endDate.after_or_equal' => 'La date de fin ne peut pas précéder la date de début.',
        ], [
            'label' => 'libellé', 'startDate' => 'date de début', 'endDate' => 'date de fin', 'place' => 'lieu',
            'name' => 'nom', 'periodicity' => 'périodicité', 'eventPlace' => 'lieu habituel',
            'yearStart' => 'année de création', 'yearEnd' => 'année de fin',
        ]);

        $year = Carbon::parse($this->startDate)->format('Y');

        $event = DB::transaction(function () use ($year) {
            if ($this->mode === 'new') {
                $event = new Event();
                $event->name = trim($this->name);
                $event->type = $this->type;
                $event->periodicity = $this->periodicity ?: null;
                $event->place = $this->nullIfEmpty($this->eventPlace);
                $event->url = $this->nullIfEmpty($this->url);
                $event->information = $this->nullIfEmpty($this->eventInformation);
                $event->is_full_scope = $this->isFullScope;
                $event->year_start = $this->yearStart ?: $year;
                $event->year_end = $this->nullIfEmpty($this->yearEnd);
                $event->save();
            } else {
                $event = Event::findOrFail($this->eventId);
                // Occurrence antérieure à la création connue : elle devient la première
                if (blank($event->year_start) || $year < $event->year_start) {
                    $event->year_start = $year;
                }
                // Occurrence postérieure à la "dernière" : l'évènement est toujours actif
                // (une occurrence ancienne ajoutée après coup ne remet pas en cause l'année de fin)
                if (filled($event->year_end) && $year >= $event->year_end) {
                    $event->year_end = null;
                }
                if ($event->isDirty()) {
                    $event->save();
                }
            }

            $occurrence = new EventOccurrence();
            $occurrence->event_id = $event->id;
            $occurrence->label = $this->nullIfEmpty($this->label);
            $occurrence->start_date = $this->startDate;
            $occurrence->end_date = $this->endDate;
            $occurrence->place = $this->nullIfEmpty($this->place);
            $occurrence->information = $this->nullIfEmpty($this->information);
            $occurrence->is_confirmed = $this->isConfirmed;
            $occurrence->save();

            return $event;
        });

        $created = $this->mode === 'new' ? 'Évènement et occurrence créés' : 'Occurrence ajoutée';
        // On reste sur l'évènement (liste des occurrences à jour, nouvelle saisie pré-remplie)
        $this->search = '';
        $this->selectEvent($event->id);
        $this->notice = "{$created} : « {$event->name} » {$year}. Vous pouvez en ajouter une autre ci-dessous, ou revenir à la recherche.";
    }

    /**
     * Avertissements (non bloquants) sur la saisie en cours.
     */
    private function warnings(?Event $event): array
    {
        $warnings = [];

        if ($this->mode === 'new' && trim($this->name) !== ''
            && Event::where('name', trim($this->name))->exists()) {
            $warnings[] = "Un évènement porte déjà exactement ce nom : vérifiez qu'il ne s'agit pas du même.";
        }

        if (!$event || $this->startDate === '' || strtotime($this->startDate) === false) {
            return $warnings;
        }

        $start = $this->startDate;
        $end = ($this->endDate !== '' && $this->endDate >= $start) ? $this->endDate : $start;
        $year = substr($start, 0, 4);

        $overlap = $event->occurrences->first(fn ($o) => $o->start_date->format('Y-m-d') <= $end && $o->end_date->format('Y-m-d') >= $start);
        if ($overlap) {
            $warnings[] = "Ces dates chevauchent une occurrence existante (du {$overlap->start_date->format('d/m/Y')} au {$overlap->end_date->format('d/m/Y')}).";
        } elseif ($event->periodicity !== EventPeriodicity::IRREGULIER
            && $event->occurrences->contains(fn ($o) => $o->start_date->format('Y') === $year)) {
            $warnings[] = "Une occurrence existe déjà en {$year}.";
        }

        return $warnings;
    }

    private function resetForm(): void
    {
        $this->reset([
            'mode', 'eventId', 'name', 'type', 'periodicity', 'eventPlace', 'url', 'eventInformation',
            'isFullScope', 'yearStart', 'yearEnd', 'label', 'startDate', 'endDate', 'place', 'information',
            'isConfirmed', 'notice', 'error',
        ]);
        $this->resetValidation();
    }

    private function nullIfEmpty(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    public function render()
    {
        $results = new Collection();
        if ($this->mode === null && mb_strlen(trim($this->search)) >= 2) {
            $results = Event::withCount('occurrences')
                ->withMax('occurrences', 'start_date')
                ->where('name', 'like', '%' . trim($this->search) . '%')
                ->orderBy('name')
                ->limit(30)
                ->get();
        }

        $event = $this->mode === 'existing' ? Event::with(['occurrences' => fn ($q) => $q->orderByDesc('start_date')])->find($this->eventId) : null;

        return view('livewire.event-occurrence-add', [
            'results' => $results,
            'event' => $event,
            'warnings' => $this->mode ? $this->warnings($event) : [],
            'canEdit' => auth()->user()?->hasMemberRole(),
            'types' => EventType::cases(),
            'periodicities' => EventPeriodicity::cases(),
        ]);
    }
}
