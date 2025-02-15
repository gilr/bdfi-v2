<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Database\Eloquent\Model;

class DynamicSelect extends Component
{
    public $limit = 10;
    public $modelClass; // Nom du modèle (ex: 'App\Models\Award')
    public $search = '';
    public $results = [];

    public function mount($modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function updatedSearch()
    {
        if (class_exists($this->modelClass)) {
            if (strlen($this->search) > 2) {
                $this->results = $this->modelClass::where('name', 'like', "%{$this->search}%")
                    ->orderBy('name')
                    ->limit($this->limit)
                    ->get();
            } else {
                $this->results = [];
            }
        }
    }

    public function render()
    {
        return view('livewire.dynamic-select');
    }
}
