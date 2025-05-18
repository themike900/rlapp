<?php

namespace App\Livewire\Pages;

use Livewire\Component;

class RlEmails extends Component
{
    public $activeTab = 'sentEmails';

    public function render()
    {
        return view('livewire.pages.rl-emails');
    }
}
