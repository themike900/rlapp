<?php

namespace App\Livewire;

use Illuminate\Http\RedirectResponse;
use Livewire\Component;

class LayoutWrapper extends Component
{

    protected $listeners = ['open-crew-edit-page' => 'setCrewPage','open-mem-edit-page' => 'setMemPage'];

    public $currentPage = 'rl-action-list';
    public $currentActionId = 0;

    public function setActivePage($page): void
    {
        $this->currentPage = $page;
    }

    public function setCrewPage(): void
    {
        $this->currentActionId = session()->get('actionID') ?? 0;
        $this->currentPage = 'rl-crew-edit';
    }

    public function setMemPage(): void
    {
        $this->currentActionId = session()->get('actionID') ?? 0;
        $this->currentPage = 'rl-mem-edit';
    }
    public function logout()
    {
        auth()->logout();
        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.layout-wrapper');
    }


}
