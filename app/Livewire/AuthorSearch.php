<?php

namespace App\Livewire;

use Livewire\WithoutUrlPagination;
use Livewire\WithPagination;
use Livewire\Component;
use App\Models\Author;

class AuthorSearch extends Component
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
        $authors = NULL;

        if ($this->search != "")
        {
            $authors = Author::where('name', 'like', '%' . $this->search . '%')
                ->select('name', 'first_name', 'birth_date', 'slug')
                ->simplePaginate(15);
        }
        return view('livewire.author-search',  [
            'authors' => $authors,
        ]);
    }
}
