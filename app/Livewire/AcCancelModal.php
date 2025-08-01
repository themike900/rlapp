<?php

namespace App\Livewire;

use App\Jobs\SendEmail;
use App\Models\Action;
use App\Models\ActionMember;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AcCancelModal extends Component
{
    public $show = false;
    public $action = [];
    public $actionId;
    //public $action_state_sc;
    public $cancel_reason;


    protected $listeners = ['open-ac-cancel-modal' => 'loadItem'];

    public function loadItem($actionId): void
    {
        //Log::debug('Loading data: '.$actionId);
        $this->actionId = $actionId;
        $action = Action::find($actionId);
        if ($action) {
            $action->action_date = Carbon::createFromFormat('Y-m-d', $action->action_date)->isoFormat('dd DD.MM.');

            $this->action = $action->toArray();

            //$this->action_state_sc = $action['action_state_sc'];
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
                'cancel_reason' => $this->cancel_reason,
                'action_state_sc' => 'af'
            ]);

        $alle_tn = DB::table('action_members')
            ->where('action_id', $this->actionId)
            ->whereIn('reg_state',['br','ang'])
            ->get();

        foreach ($alle_tn as $tn) {
            dispatch(new SendEmail($tn->web_id, 'fahrt-absage', ['action_id' => $this->actionId]));
            ActionMember::deleteRecord($this->actionId, $tn->web_id);
        }

        $this->dispatch('refreshTable');
        $this->close();
    }

    public function render(): View
    {
        return view('livewire.ac-cancel-modal');
    }
}
