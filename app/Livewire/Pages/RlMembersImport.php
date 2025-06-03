<?php

namespace App\Livewire\Pages;

use App\Jobs\SendEmail;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]

class RlMembersImport extends Component
{
    public function render(): View
    {
        return view('livewire.pages.rl-members-import');
    }
}
