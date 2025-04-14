<?php

namespace App\Livewire;

use App\Models\Action;
use App\Services\ParticipantsListService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AcViewModal extends Component
{
    public $show = false;
    public $action = [];
    public $actionId;
    public $members = [];


    protected $listeners = ['open-ac-view-modal' => 'loadItem'];

    public function loadItem($actionId): void
    {
        //Log::debug('Loading data: '.$actionId);
        $this->actionId = $actionId;
        $action = Action::find($actionId);
        if ($action) {
            $this->action = $action->toArray();

            $service = new ParticipantsListService();
            $members = $service->getParticipantsList($actionId);
            $members['participants']    = implode(", ", $members['participants']);
            $members['participants_wl'] = implode(", ", $members['participants_wl']);
            $members['crew']            = implode(", ", $members['crew']);
            $members['service']         = implode(", ", $members['service']);
            $this->members = $members;

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

    public function render()
    {
        return view('livewire.ac-view-modal');
    }
}
