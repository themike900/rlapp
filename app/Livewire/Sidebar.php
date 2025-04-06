<?php

namespace App\Livewire;

use Livewire\Component;

class Sidebar extends Component
{

    public $currentPage = 'rl-action-list';

    public function setActivePage(string $page): void
    {
        $this->currentPage = $page;
    }

    public function render()
    {
        return view('livewire.sidebar');
    }
}
