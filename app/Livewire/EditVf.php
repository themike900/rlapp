<?php

namespace App\Livewire;

use App\Models\Action;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;
//use Illuminate\Support\Carbon;

class EditVf extends Component
{
    public $ac_type;

    public $action_id;
    public string $action_name = "";
    public string $action_type_sc = "";
    public string $action_date;
    public string $crew_start_at = "00:00";
    public string $crew_end_at = "00:00";
    public string $action_start_at = "00:00";
    public string $action_end_at = "00:00";
    public string $ice_info = "";
    public string $additional_info = "";
    public string $ac_with_wl = "0";
    public string $ac_max_guests = "0";

    public function mount($action_id): void
    {
        $action = Action::find($action_id);
        $this->action_id = $action_id;
        $this->action_name = $action->action_name;
        $this->ac_type = $action->action_type_sc;
        $this->action_type_sc = $action->action_type_sc;
        $this->action_date = $action->action_date;
        $this->crew_start_at = $action->crew_start_at;
        $this->crew_end_at = $action->crew_end_at;
        $this->action_start_at = $action->action_start_at;
        $this->action_end_at = $action->action_end_at;
        $this->ice_info = $action->ice_info ?? '';
        $this->additional_info = $action->additional_info ?? '';
        $this->ac_with_wl = $action->ac_with_wl;
        $this->ac_max_guests = $action->ac_max_guests;
    }

    public function save()
    {
        DB::table('actions')->where('id', $this->action_id)
            ->update([
            'action_name' => $this->action_name,
            'action_type_sc' => $this->action_type_sc,
            'action_date' => $this->action_date,
            'crew_start_at' => $this->crew_start_at,
            'crew_end_at' => $this->crew_end_at,
            'action_start_at' => $this->action_start_at,
            'action_end_at' => $this->action_end_at,
            'ice_info' => $this->ice_info,
            'additional_info' => $this->additional_info,
            //'created_at' => now(),
            'updated_at' => now(),
            //'action_state_sc' => 'br',
            //'ac_max_pers' => 30,
            //'ac_reg_state_cr' => 'crbr',
            //'ac_reg_state_sv' => 'svbr',
            //'ac_reg_state_tn' => 'tnon',
            'ac_max_guests' => $this->ac_max_guests,
            'ac_with_wl' => $this->ac_with_wl,
        ]);

        //$this->reset();
        session()->flash('message', 'Neue Gästefahrt erfolgreich gespeichert');
        session()->flash('last_action_id', $this->action_id);

        $this->dispatch('refreshTable');
        $this->dispatch('close-ac-edit-modal');
    }

    public function render(): View
    {
        return view('livewire.edit-vf');
    }
}
