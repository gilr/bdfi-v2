<?php

namespace App\Livewire;

use Livewire\Component;

class CoverSlide extends Component
{
    public $isOpen = false;
    public $currentIndex = 0;
    public $covers = [];

    public function mount($covers)
    {
        $this->covers = $covers;
    }

    public function openCover($index)
    {
        $this->currentIndex = $index;
        $this->isOpen = true;
    }

    public function closeCover()
    {
        $this->isOpen = false;
    }

    public function nextCover()
    {
        $this->currentIndex = ($this->currentIndex + 1) % count($this->covers);
    }

    public function prevCover()
    {
        $this->currentIndex = ($this->currentIndex - 1 + count($this->covers)) % count($this->covers);
    }

    public function render()
    {
        return view('livewire.cover-slide');
    }
}
