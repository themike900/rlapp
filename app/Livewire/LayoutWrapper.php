<?php

namespace App\Livewire;

use Livewire\Component;

class LayoutWrapper extends Component
{
    public $currentPage = 'rl-action-list';
    public $currentActionId = 0;

    public function setActivePage($page)
    {
        $this->currentPage = $page;
    }

    public function render()
    {
        return view('livewire.layout-wrapper');
    }

    public function logout(): \Illuminate\Http\RedirectResponse
    {
        auth()->logout();
        return redirect()->route('login');
    }

}
