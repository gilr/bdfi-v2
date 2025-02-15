<?php

namespace App\Livewire;

use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Livewire\Component;
use App\Models\Publication;

class PublicationSearch extends Component
{
    use WithPagination, WithoutUrlPagination;

    public $showModal = false;
    public $search = ''; // Contient l'extrait saisi par l'utilisateur
    public $intro;
    public $label;

    public function mount($intro = "", $label = "Rechercher")
    {
        $this->intro = $intro;
        $this->label = $label;
    }

    // Met à jour la recherche et exécute la recherche à chaque modification de "search"
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $publications = NULL;

        if ($this->search != "")
        {
            $publications = Publication::where('name', 'like', "%{$this->search}%")
                ->orderBy('name', 'asc')
                ->with(['publisher'])
                ->simplePaginate(15);
        }
        return view('livewire.publication-search',  [
            'publications' => $publications,
        ]);
    }
}
