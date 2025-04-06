<?php

namespace App\Livewire;

use Livewire\Component;

class DynamicContent extends Component
{
    public $page;
    public function render()
    {
        return view('livewire.dynamic-content');

    }
}
