<?php

namespace App\Livewire\Pages;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class RlMembersList extends Component
{
    public function render()
    {
        return view('livewire.pages.rl-members-list');
    }
}
