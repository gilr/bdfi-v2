<?php

namespace App\Livewire;

use Livewire\Component;

class PublicationCarousel extends Component
{
    public $images = [];
    public $currentIndex = 0;

    public function mount($images)
    {
        $this->images = $images;
    }

    public function next()
    {
        $this->currentIndex = ($this->currentIndex + 1) % count($this->images);
    }

    public function prev()
    {
        $this->currentIndex = ($this->currentIndex - 1 + count($this->images)) % count($this->images);
    }

    public function render()
    {
        return view('livewire.publication-carousel');
    }
}
