<?php
// app/Livewire/EditModal.php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Action;
use Illuminate\View\View;

class EditModal extends Component
{
    public $show = false;
    public $action = [];
    public $actionId;


    protected $listeners = ['open-ac-edit-modal' => 'loadItem'];

    public function loadItem($actionId): void
    {
        //Log::debug('Loading data: '.$actionId);
        $this->actionId = $actionId;
        $action = Action::find($actionId);
        if ($action) {
            $this->action = $action->toArray();
        } else {
            $this->action = [];
        }

        $this->show = true;
        //Log::debug('Showing data: '.$this->action["action_name"]);
    }

    public function close(): void
    {
        $this->show = false;
    }

    public function save(): void
    {
        //$this->action->save();
        $this->dispatch('refreshTable');
        $this->close();
    }

    public function render(): View
    {
        //Log::debug('Rendering Modal');
        return view('livewire.edit-modal');
    }
}
