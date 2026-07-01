<?php

namespace App\Livewire\Pages;

use App\Jobs\SendEmail;
use App\Jobs\SendSms;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]

class RlMembersImport extends Component
{
    public function testSMS():void
    {
        dispatch(new SendSms(322, 'fahrt-absage-sms', ['action_id' => 170]));
        Log::debug('Test-Absage-SMS an Michael ');
        ;
    }

    public function render(): View
    {
        return view('livewire.pages.rl-members-import');
    }

}
