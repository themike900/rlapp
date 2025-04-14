<?php
// app\Livewire\ActionsTable.php

namespace App\Livewire;

use App\Models\Action;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

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
    public function openCrewModal($actionId): void
    {
        //Log::debug('openCrewModal, actionID: ' . $actionId);

        $this->selectedActionId = $actionId;
        $this->dispatch('open-ac-crew-modal', actionId: $actionId);
    }
    public function openStatusModal($actionId): void
    {
        //Log::debug('openCrewModal, actionID: ' . $actionId);

        $this->selectedActionId = $actionId;
        $this->dispatch('open-ac-status-modal', actionId: $actionId);
    }

    public function render(): View
    {
        Log::debug('Filter: '.$this->filter);

        $actions = Action::query()
            ->join('action_states', 'actions.action_state_sc', '=', 'action_states.sc')
            ->whereIn('action_state_sc', explode(',',$this->filter))
            ->orderBy('action_date')
            ->select('actions.*', 'action_states.name as action_state_name')
            ->get();

        foreach ($actions as $action) {
            $action->action_date = Carbon::createFromFormat('Y-m-d', $action->action_date)->isoFormat('dd DD.MM.');
        }
        $this->actions = $actions;

        return view('livewire.actions-table', compact('actions'));
    }

}
