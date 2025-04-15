<?php

namespace App\Livewire;

use App\Models\Action;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;
//use Illuminate\Support\Carbon;

class EditGf extends Component
{
    public $selectedForm;

    public int $action_id = 0;
    public string $action_name = "";
    public string $action_type_sc = "";
    public string $action_date = '';
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
    public string $confirm_date = '';

    public function mount($action_id): void
    {
        $action = Action::find($action_id);
        $this->action_id = $action_id;
        $this->action_name = $action->action_name;
        //$this->ac_type = $action->action_type_sc;
        $this->action_type_sc = $action->action_type_sc;
        $this->action_date = $action->action_date;
        $this->crew_start_at = $action->crew_start_at;
        $this->crew_end_at = $action->crew_end_at;
        $this->action_start_at = $action->action_start_at;
        $this->action_end_at = $action->action_end_at;
        $this->additional_info = $action->additional_info ?? '';
        //$this->ac_with_wl = $action->ac_with_wl;
        //$this->ac_max_pers = $action->ac_max_pers;
        $this->guest_count = $action->guest_count ?? 0;
        $this->reason = $action->reason ?? '';
        $this->applicant_name = $action->applicant_name ?? '';
        $this->applicant_email = $action->applicant_email ?? '';
        $this->applicant_phone = $action->applicant_phone ?? '';
        $this->contact_name = $action->contact_name ?? '';
        $this->contact_email = $action->contact_email ?? '';
        $this->contact_phone = $action->contact_phone ?? '';
        $this->invoice_address = $action->invoice_address ?? '';
        $this->invoice_amount = $action->invoice_amount ?? '';
        $this->catering_info = $action->catering_info ?? '';
        $this->ice_info = $action->ice_info ?? '';
        $this->confirm_date = $action->confirm_date ?? '';
        $this->crew_supply = $action->crew_supply ?? '';
    }

    public function save(): void {
        // Dummy zum Zwischenspeicher in den Properties bei Tabwechsel
    }
    public function savedb(): void
    {

        DB::table('actions')->where('id',$this->action_id)->update([
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
            //'ac_max_pers' => $this->ac_max_pers,
            //'ac_reg_state_cr' => '',
            //'ac_reg_state_sv' => '',
            //'ac_reg_state_tn' => 'tnon',
            //'ac_max_guests' => 0,
            //'ac_with_wl' => $this->ac_with_wl,
            'guest_count' => $this->guest_count,
            'reason' => $this->reason,
            'applicant_name' => $this->applicant_name,
            'applicant_email' => $this->applicant_email,
            'applicant_phone' => $this->applicant_phone,
            'invoice_address' => $this->invoice_address,
            'invoice_amount' => $this->invoice_amount,
            'catering_info' => $this->catering_info,
            'contact_name' => $this->contact_name,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'confirm_date' => $this->confirm_date,
            'crew_supply' => $this->crew_supply,
        ]);

        //$this->reset();
        session()->flash('message', 'Neue Gästefahrt erfolgreich gespeichert');
        session()->flash('last_action_id', $this->action_id);

        $this->dispatch('refreshTable');
        $this->dispatch('close-ac-edit-modal');
        //$this->dispatch('selectedForm', "");

    }

    public function render(): View
    {
        return view('livewire.edit-gf');
    }
}
