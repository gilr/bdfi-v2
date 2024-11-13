<?php

namespace App\Livewire;

use Livewire\Component;

class CollapsibleBlock extends Component
{
    public $isExpanded = false;
    public $blockTitle;
    public $blockIntro;
    public $blockContent;

    public function mount($title = '', $intro = '', $content = '')
    {
        $this->blockTitle = $title;
        $this->blockIntro = $intro;
        $this->blockContent = $content;
    }

    public function toggle()
    {
        $this->isExpanded = !$this->isExpanded;
    }

    public function render()
    {
        return view('livewire.collapsible-block');
    }
}
