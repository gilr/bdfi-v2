<?php

namespace App\Livewire;

use Livewire\Component;

class CoverZoom extends Component
{
    public $isOpen = false;
    public $image;

    public function mount($image)
    {
        $this->image = $image;
    }
    public function toggleCover()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function render()
    {
        return view('livewire.cover-zoom');
    }
}
