<?php

namespace App\Livewire;

use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;
use App\Enums\AnnouncementType;
use App\Models\Announcement;

/**
 * Ajout d'une annonce, d'un point (historique, aides, statistiques) ou d'un remerciement (table announcements).
 * L'aide du champ "Titre / Sujet" dépend du type choisi. Pour repère, les dernières saisies du même type
 * sont affichées, ainsi que, pour un remerciement ou une consécration, les saisies existantes au même nom.
 */
class AnnouncementAdd extends Component
{
    public string $type = 'remerciement';
    public string $date = '';
    public string $name = '';
    public string $url = '';
    public string $information = '';

    public string $notice = '';

    // Obligatoire pour interdire les bots pirateurs
    protected static $middlewares = [
        'auth.bdfiadmin',
        'admin.maintenance',
    ];

    // Types dont le "titre" est une personne (prénom + nom, ou pseudo)
    private const PERSON_TYPES = ['remerciement', 'consecration'];

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->hasMemberRole(), 403);
        $this->notice = '';

        $this->validate([
            'type' => ['required', Rule::enum(AnnouncementType::class)],
            'date' => 'required|date',
            'name' => 'required|string|max:64',
            'url' => 'nullable|url|max:256',
            'information' => 'required|string|max:65535',
        ], [
            'url.url' => "L'URL n'est pas valide (elle doit commencer par https:// ou http://).",
        ], [
            'name' => 'titre / sujet', 'url' => 'URL', 'information' => 'description',
        ]);

        $announcement = new Announcement();
        $announcement->type = $this->type;
        $announcement->date = $this->date;
        $announcement->name = trim($this->name);
        $announcement->url = trim($this->url) ?: null;
        $announcement->information = trim($this->information);
        $announcement->save();

        $this->notice = AnnouncementType::from($this->type)->getLabel() . " « {$announcement->name} » du "
            . $announcement->date->format('d/m/Y') . ' enregistré.';
        // Type et date conservés pour enchaîner plusieurs saisies
        $this->reset(['name', 'url', 'information']);
        $this->resetValidation();
    }

    private function nameHelp(): string
    {
        return match ($this->type) {
            'remerciement', 'consecration' => 'Prénom + nom, ou pseudo.',
            'point_histo', 'autre' => 'Période (mois, trimestre), par exemple « Avril 2014 ».',
            default => 'Sujet.',
        };
    }

    private function dateHelp(): string
    {
        return $this->type === 'remerciement'
            ? "Date de réception de l'aide."
            : "Date de l'annonce ou de l'information.";
    }

    public function render()
    {
        $recent = Announcement::where('type', $this->type)->orderByDesc('date')->orderByDesc('id')->limit(8)->get();

        $sameName = new Collection();
        if (in_array($this->type, self::PERSON_TYPES, true) && mb_strlen(trim($this->name)) >= 3) {
            $sameName = Announcement::whereIn('type', self::PERSON_TYPES)
                ->where('name', 'like', '%' . trim($this->name) . '%')
                ->orderByDesc('date')->limit(10)->get();
        }

        return view('livewire.announcement-add', [
            'types' => AnnouncementType::cases(),
            'nameHelp' => $this->nameHelp(),
            'dateHelp' => $this->dateHelp(),
            'recent' => $recent,
            'sameName' => $sameName,
            'canEdit' => auth()->user()?->hasMemberRole(),
        ]);
    }
}
