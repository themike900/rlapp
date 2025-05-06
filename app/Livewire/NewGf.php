<?php

namespace App\Livewire;

use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;
//use Illuminate\Support\Carbon;

class NewGf extends Component
{
    public $selectedForm;

    public string $action_name = "";
    public string $action_type_sc = "";
    public string $action_date;
    public string $crew_start_at = "00:00";
    public string $crew_end_at = "00:00";
    public string $action_start_at = "00:00";
    public string $action_end_at = "00:00";
    public string $reason = "";
    public string $applicant_name = "";
    public string $applicant_email = "" ;
    public string $applicant_phone = "";
    public string $contact_name = "";
    public string $contact_email = "";
    public string $contact_phone = "";
    public string $invoice_address = "";
    public string $invoice_amount = "";
    public string $catering_info = "Gäste bringen Proviant mit";
    public string $ice_info = "Bitte Eis besorgen";
    public string $crew_supply = "Crew ist eingeladen";
    public string $additional_info = "";
    public string $guest_count = "0";
    public string $confirm_date;

    public function mount($selectedForm): void
    {
        $this->action_name = DB::table('action_types')->where('sc', "$selectedForm")->value('name');
        $this->selectedForm = $selectedForm;
        $this->action_type_sc = $selectedForm;
        if ($selectedForm == "gfx") $this->invoice_amount = "800";
        if ($selectedForm == "gfm") $this->invoice_amount = "400";
    }

    public function save(): void
    {

        $action_id = DB::table('actions')->insert([
            'action_name' => $this->action_name,
            'action_type_sc' => $this->action_type_sc,
            'action_date' => $this->action_date,
            'crew_start_at' => $this->crew_start_at,
            'crew_end_at' => $this->crew_end_at,
            'action_start_at' => $this->action_start_at,
            'action_end_at' => $this->action_end_at,
            'reason' => $this->reason,
            'applicant_name' => $this->applicant_name,
            'applicant_email' => $this->applicant_email,
            'applicant_phone' => $this->applicant_phone,
            'contact_name' => $this->contact_name,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'invoice_address' => $this->invoice_address,
            'invoice_amount' => $this->invoice_amount,
            'catering_info' => $this->catering_info,
            'ice_info' => $this->ice_info,
            'crew_supply' => $this->crew_supply,
            'additional_info' => $this->additional_info,
            'guest_count' => $this->guest_count,
            'confirm_date' => $this->confirm_date ?? null,
            'created_at' => now(),
            'updated_at' => now(),
            'action_state_sc' => 'iv',
            'ac_max_pers' => 30,
            'ac_reg_state_cr' => 'crbr',
            'ac_reg_state_sv' => 'svbr',
            'ac_reg_state_tn' => 'tnon',
            'ac_max_guests' => 0,
            'ac_with_wl' => 0,
        ]);

        $this->reset();
        session()->flash('message', 'Neue Gästefahrt erfolgreich gespeichert');
        session()->flash('last_action_id', $action_id);

        $this->dispatch('selectedForm', "");
    }

    public function render(): View
    {
        return view('livewire.new-gf');
    }
}
