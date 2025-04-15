<?php

namespace App\Livewire;

use App\Models\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
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
        DB::table('actions')
            ->where('id', $this->actionId)
            ->update([
                 'action_state_sc' => $this->action_state_sc
            ]);

        $this->dispatch('refreshTable');
        $this->close();
    }

    public function render(): View
    {
        return view('livewire.ac-status-modal');
    }
}
