<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Author;

class AuthorSearchAndEdit extends Component
{
    public $search = ''; // Contient l'extrait saisi par l'utilisateur
    public $authors= [];
    public $selectedPerson = null;
    public $name = '';
    public $first_name = '';
    public $birth_date = '';
    public $birthplace = '';
    public $date_death = '';
    public $bio = '';

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
            $this->birth_date = $this->selectedPerson->birth_date;
            $this->birthplace = $this->selectedPerson->birthplace;
            $this->date_death = $this->selectedPerson->date_death;
            $this->bio = $this->selectedPerson->information;
        }
    }

    public function save()
    {
        // Valider les données du formulaire
        $this->validate([
            'birth_date'    => ['required', 'size:10', 'regex:/[\-012][\-0-9]{3}-([0-9]{2}-[0-9]{2}|circa)/'],
            'date_death'    => ['required', 'size:10', 'regex:/[\-012][\-0-9]{3}-([0-9]{2}-[0-9]{2}|circa)/'],
            'birthplace'    => 'max:64',
            'bio'           => 'max:65535',
        ]);

        // Mettre à jour l'enregistrement sélectionné
        if ($this->selectedPerson) {
            $this->selectedPerson->update([
                'birth_date' => $this->birth_date,
                'birthplace' => $this->birthplace,
                'date_death' => $this->date_death,
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
        $this->birth_date = '';
        $this->birthplace = '';
        $this->date_death = '';
        $this->bio = '';
        $this->authors = [];
    }

    public function render()
    {
        return view('livewire.author-search-and-edit');
    }
}
