<?php

namespace App\Livewire;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Illuminate\Contracts\View\Factory;


class Modal extends Component
{
    public bool $isOpen = false;

    public function open(): void
    {
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
    }
    public function render(): Application|Factory|\Illuminate\Contracts\View\View|View
    {
        return view('livewire.modal');
    }
}
