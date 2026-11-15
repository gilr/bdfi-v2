<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Route;

use Livewire\Component;
use App\Models\Author;
use App\Models\Country;
use App\Enums\AuthorGender;
use DB;

class AuthorSearchAndEdit extends Component
{
    /** Date : AAAA-MM-JJ (MM/JJ à 00 si inconnus), ou AAAA-circa / -AAA-circa ; vide => inconnue (0000-00-00) */
    public const DATE_REGEX = '/^(\d{4}-\d{2}-\d{2}|-?\d{1,4}-circa)$/';

    public $search = ''; // Contient l'extrait saisi par l'utilisateur
    public $authors = [];
    public $selectedPerson = null;
    public $name = '';
    public $first_name = '';
    public $legal_name = '';
    public $country_id = null;
    public $gender = '';
    public $is_pseudonym = false;
    public $birth_date = '';
    public $birthplace = '';
    public $date_death = '';
    public $alt_names = '';
    public $bio = '';

    // Obligatoire pour interdire les bots pirateurs
    protected static $middlewares = [
        'auth.bdfiadmin',
        'admin.maintenance',
    ];

    public function updatedSearch()
    {
        if (!empty($this->search)) {
            $this->authors = Author::where('name', 'like', "%{$this->search}%")
                ->orderBy('name', 'asc')
                ->limit(24)
                ->get();
        } else {
            $this->authors = [];
        }
    }

    public function selectAuthor($authorId)
    {
        $this->selectedPerson = Author::find($authorId);
        if ($this->selectedPerson) {
            $this->name = $this->selectedPerson->name;
            $this->first_name = $this->selectedPerson->first_name;
            $this->legal_name = $this->selectedPerson->legal_name;
            $this->country_id = $this->selectedPerson->country_id;
            $this->gender = $this->selectedPerson->gender?->value ?? $this->selectedPerson->gender;
            $this->is_pseudonym = (bool) $this->selectedPerson->is_pseudonym;
            $this->birth_date = $this->selectedPerson->birth_date;
            $this->birthplace = $this->selectedPerson->birthplace;
            $this->date_death = $this->selectedPerson->date_death;
            $this->alt_names = $this->selectedPerson->alt_names;
            $this->bio = $this->selectedPerson->information;
        }
    }

    public function save()
    {
        // Modification réservée aux membres et plus (comme la création)
        abort_unless(auth()->user()?->hasMemberRole(), 403);

        // Valider les données du formulaire (mêmes règles que la création, cf. StoreAuthorRequest)
        $this->validate([
            'name'          => ['required', 'max:32'],
            'first_name'    => ['nullable', 'max:32'],
            'legal_name'    => ['nullable', 'max:128'],
            'country_id'    => ['required', 'integer', 'exists:countries,id'],
            'gender'        => [\Illuminate\Validation\Rule::enum(AuthorGender::class)],
            'is_pseudonym'  => ['nullable', 'boolean'],
            'birth_date'    => ['nullable', 'max:10', 'regex:' . self::DATE_REGEX],
            'birthplace'    => ['nullable', 'max:64'],
            'date_death'    => ['nullable', 'max:10', 'regex:' . self::DATE_REGEX],
            'alt_names'     => ['nullable', 'max:512'],
            'bio'           => ['nullable', 'max:65535'],
        ], [
            'birth_date.regex' => 'Date de naissance : formats acceptés AAAA-MM-JJ (00 si inconnu), AAAA-circa ou -AAA-circa.',
            'date_death.regex' => 'Date de décès : formats acceptés AAAA-MM-JJ (00 si inconnu), AAAA-circa ou -AAA-circa.',
            'country_id.*'     => 'Nationalité : choisissez un pays de la liste.',
        ]);

        // Mettre à jour l'enregistrement sélectionné
        if ($this->selectedPerson) {
            $this->selectedPerson->update([
                'name' => trim($this->name),
                'first_name' => filled($this->first_name) ? trim($this->first_name) : null,
                'legal_name' => filled($this->legal_name) ? trim($this->legal_name) : null,
                'country_id' => $this->country_id,
                'gender' => $this->gender,
                'is_pseudonym' => (bool) $this->is_pseudonym,
                'birth_date' => filled($this->birth_date) ? $this->birth_date : '0000-00-00',
                'birthplace' => $this->birthplace,
                'date_death' => filled($this->date_death) ? $this->date_death : '0000-00-00',
                'alt_names' => filled($this->alt_names) ? trim($this->alt_names) : null,
                'information' => $this->bio,
            ]);

            session()->flash('success', 'Modifications enregistrées avec succès.');
        }
    }

    public function resetSelection()
    {
        $this->search = '';
        $this->selectedPerson = null;
        $this->name = '';
        $this->first_name = '';
        $this->legal_name = '';
        $this->country_id = null;
        $this->gender = '';
        $this->is_pseudonym = false;
        $this->birth_date = '';
        $this->birthplace = '';
        $this->date_death = '';
        $this->alt_names = '';
        $this->bio = '';
        $this->authors = [];
    }

    /**
     * Pays pour la liste du formulaire : les plus fréquents chez les auteurs, puis tous les autres
     * par ordre alphabétique (même logique que AuthorController::countryOptions).
     * @return array{frequent: \Illuminate\Support\Collection, others: \Illuminate\Support\Collection}
     */
    private function countryOptions(): array
    {
        $frequentIds = DB::table('authors')->whereNull('deleted_at')->whereNotNull('country_id')
            ->select('country_id', DB::raw('count(*) as n'))->groupBy('country_id')
            ->orderByDesc('n')->limit(8)->pluck('country_id')->all();

        $all = Country::orderBy('name')->get(['id', 'name'])->reject(fn ($c) => $c->name === '?');

        return [
            'frequent' => collect($frequentIds)->map(fn ($id) => $all->firstWhere('id', $id))->filter()->values(),
            'others'   => $all->reject(fn ($c) => in_array($c->id, $frequentIds))->values(),
        ];
    }

    public function render()
    {
        return view('livewire.author-search-and-edit', [
            'countries' => $this->countryOptions(),
            'genders' => AuthorGender::cases(),
        ]);
    }
}
