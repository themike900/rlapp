<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;
//use Illuminate\Support\Carbon;

class NewVf extends Component
{
    public $selectedForm;

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

    public function mount($selectedForm): void
    {
        $this->action_name = DB::table('action_types')->where('sc', "$selectedForm")->value('name');
        $this->selectedForm = $selectedForm;
        $this->action_type_sc = $selectedForm;
    }

    public function save()
    {

        $action_id = DB::table('actions')->insert([
            'action_name' => $this->action_name,
            'action_type_sc' => $this->action_type_sc,
            'action_date' => $this->action_date,
            'crew_start_at' => $this->crew_start_at,
            'crew_end_at' => $this->crew_end_at,
            'action_start_at' => $this->action_start_at,
            'action_end_at' => $this->action_end_at,
            'ice_info' => $this->ice_info,
            'additional_info' => $this->additional_info,
            'created_at' => now(),
            'updated_at' => now(),
            'action_state_sc' => 'br',
            'ac_max_pers' => 30,
            'ac_reg_state_cr' => 'crbr',
            'ac_reg_state_sv' => 'svbr',
            'ac_reg_state_tn' => 'tnon',
            'ac_max_guests' => 0,
            'ac_with_wl' => $this->ac_with_wl,
        ]);

        $this->reset();
        session()->flash('message', 'Neue Gästefahrt erfolgreich gespeichert');
        session()->flash('last_action_id', $action_id);

        $this->dispatch('selectedForm', "");
    }

    public function render(): View
    {
        return view('livewire.new-vf');
    }
}
