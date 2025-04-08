<?php

namespace App\Livewire\Pages;

use Illuminate\View\View;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]

class RlActionNew extends Component
{
    protected $listeners = ['selectedForm' => 'setSelectedForm'];

    public string $selectedForm = "";

    public function setSelectedForm(string $form): void
    {
        $this->selectedForm = $form;
    }

    public function render(): View
    {
        return view('livewire.pages.rl-action-new');
    }
}
