<?php

namespace App\Livewire;

use App\Models\Action;
use App\Services\ParticipantsCalcService;
use App\Services\ParticipantsListService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AcViewModal extends Component
{
    public $show = false;
    public $action = [];
    public $actionId;
    public $members = [];
    public $cnt = [];


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

            Log::debug('members: '. print_r($members, true));

            $cnt_srv = new ParticipantsCalcService();
            $this->cnt = $cnt_srv->counts($actionId);

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

    public function render(): View
    {
        return view('livewire.ac-view-modal');
    }
}
