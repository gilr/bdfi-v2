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

    public function nextImage()
    {
        $this->currentIndex = ($this->currentIndex + 1) % count($this->images);
    }

    public function prevImage()
    {
        $this->currentIndex = ($this->currentIndex - 1 + count($this->images)) % count($this->images);
    }

    public function setImage($index)
    {
        $this->currentIndex = $index;
    }

    public function render()
    {
        return view('livewire.publication-carousel');
    }
}
