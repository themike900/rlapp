<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Component;

class DynamicContent extends Component
{
    public $page;
    public function render(): View
    {
        return view('livewire.dynamic-content');

    }
}
