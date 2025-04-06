<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]

class RlActionNew extends Component
{
    public function render()
    {
        return view('livewire.pages.rl-action-new');
    }
}
