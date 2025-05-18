<?php

namespace App\Livewire\Pages;

use App\Jobs\SendEmail;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]

class RlMembersImport extends Component
{
    public function emailTest(): void
    {
        $web_id = 322;
        $templateName = 'crew-zusage';
        $data = ['action_id' => 18];

        dispatch(new SendEmail($web_id, $templateName, $data));

    }
    public function render(): View
    {
        return view('livewire.pages.rl-members-import');
    }
}
