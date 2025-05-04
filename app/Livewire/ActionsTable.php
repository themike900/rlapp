<?php
// app\Livewire\ActionsTable.php

namespace App\Livewire;

use App\Models\Action;
use App\Services\ParticipantsCalcService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;

class ActionsTable extends Component
{
    public $filter = 'of,gs';
    public $selectedActionId;
    public $actions;

    protected $listeners = ['refreshTable' => '$refresh'];

    public function openEditModal($actionId): void
    {
        //Log::debug('openEditModal, actionID: ' . $actionId);

        $this->selectedActionId = $actionId;
        $this->dispatch('open-ac-edit-modal', actionId: $actionId);
    }
    public function openViewModal($actionId): void
    {
        //Log::debug('openViewModal, actionID: ' . $actionId);

        $this->selectedActionId = $actionId;
        $this->dispatch('open-ac-view-modal', actionId: $actionId);
    }
    public function openCrewPage($actionId): void
    {
        Log::debug('openCrewPage.actionID: ' . $actionId);
        session()->put('actionID', $actionId);

        $this->selectedActionId = $actionId;
        $this->dispatch('open-crew-edit-page');

    }
    public function openTeilnehmerPage($actionId): void
    {
        Log::debug('openTeilnehmerPage.actionID: ' . $actionId);
        session()->put('actionID', $actionId);

        $this->selectedActionId = $actionId;
        $this->dispatch('open-mem-edit-page');

    }
    public function openStatusModal($actionId): void
    {
        //Log::debug('openStatusModal, actionID: ' . $actionId);

        $this->selectedActionId = $actionId;
        $this->dispatch('open-ac-status-modal', actionId: $actionId);
    }

    public function openMembersModal($actionId): void
    {
        //Log::debug('openMembersModal, actionID: ' . $actionId);

        $this->selectedActionId = $actionId;
        $this->dispatch('open-ac-members-modal', actionId: $actionId);
    }

    public function render(): View
    {
        //Log::debug('Filter: '.$this->filter);

        $actions = Action::query()
            ->join('action_states', 'actions.action_state_sc', '=', 'action_states.sc')
            ->whereIn('action_state_sc', explode(',',$this->filter))
            ->orderBy('action_date')
            ->orderBy('action_start_at')
            ->select('actions.*', 'action_states.name as action_state_name')
            ->get();

        $service = new ParticipantsCalcService();
        foreach ($actions as $action) {
            $action->action_date = Carbon::createFromFormat('Y-m-d', $action->action_date)->isoFormat('dd DD.MM.');
            $action->cnt = $service->counts($action->id);
        }
        $this->actions = $actions;

        return view('livewire.actions-table', compact('actions'));
    }

}
