<?php

namespace App\Livewire;

use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Livewire\Component;
use App\Models\Collection;

class CollectionSearch extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $showModal = false;
    public $search = ''; // Contient l'extrait saisi par l'utilisateur
    public $intro;
    public $label;
    public $withId;

    public function mount($intro = "", $label = "Rechercher", $withId = "FALSE")
    {
        $this->intro = $intro;
        $this->label = $label;
        $this->withId = $withId;
    }

    // Met à jour la recherche et exécute la recherche à chaque modification de "search"
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $collections = NULL;

        if ($this->search != "")
        {
            $collections = Collection::where('name', 'like', "%{$this->search}%")
                ->orWhere('alt_names', 'like', "%{$this->search}%")
                ->orderBy('name', 'asc')
                ->with(['publisher', 'publisher2', 'publisher3'])
                ->simplePaginate(15);
        }
        return view('livewire.collection-search',  [
            'collections' => $collections,
        ]);
    }
}
