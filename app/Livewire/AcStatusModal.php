<?php

namespace App\Livewire;

use App\Models\Action;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AcStatusModal extends Component
{
    public $show = false;
    public $action = [];
    public $actionId;
    public $action_state_sc;


    protected $listeners = ['open-ac-status-modal' => 'loadItem'];

    public function loadItem($actionId): void
    {
        //Log::debug('Loading data: '.$actionId);
        $this->actionId = $actionId;
        $action = Action::find($actionId);
        if ($action) {
            $this->action = $action->toArray();
            $this->action_state_sc = $action['action_state_sc'];
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

    public function render()
    {
        return view('livewire.ac-status-modal');
    }
}
