<?php

namespace App\Livewire\Pages;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RlCrewEdit extends Component
{
    public $action = null;
    public $actionId = null;
    public $crew = null;
    public $crewSelections = [];
    public $newCrewSelections = [];
    public $service = null;
    public $captain = 0;
    public $captainName = '';
    public $newCaptain = 0;
    public $newCaptainName = '';
    public $selectActions = null;
    public $captains;

    /* **************************************
     *    mount($actionId)
     ****************************************/
    public function mount($actionId = null): void
    {
        Log::debug("--- RlCrewEdit.mount ----------------------------");

        $this->actionId = $actionId;

        if (empty($this->actionId)) {
            // actionId aus session holen, wenn vorhanden
            Log::debug('actionId aus session: '.$this->actionId);
        }

        $this->selectActions = DB::table("actions")
            ->whereIn('action_state_sc', ['of', 'gs'])
            ->whereIn('action_type_sc', ['vf', 'af', 'uf', 'gfx'])
            ->orderBy('action_date')
            ->orderBy('action_start_at')
            ->get(['id', 'action_name', 'action_date', 'action_start_at', 'action_end_at']);
        Log::debug('actions für select aus DB : '.print_r($this->selectActions, true));
        $this->actionId = $this->selectActions[0]->id;

        $this->captains = DB::table('members')
            ->whereLike('groups', '%sf%')
            ->orderBy('firstname')
            ->select('webid', 'firstname', 'name', 'nickname', DB::raw("
                CASE
                    WHEN nickname IS NOT NULL AND nickname != '' THEN CONCAT(nickname, ' ', name)
                    ELSE CONCAT(firstname, ' ', name)
                END AS display_name"))
            ->get();
        Log::debug('captains für select aus DB : '.print_r($this->captains, true));

    }

    /* **************************************
     *    saveCaptain()
     ****************************************/
    public function saveCaptain(): void
    {
        Log::debug("--- RlCrewEdit.saveCaptain ------------------------------");
        Log::debug('captain.webid: '.$this->captain);
        Log::debug('newCaptain.webid: '.$this->newCaptain);
        Log::debug('ac_reg_state_cr: '.$this->action->ac_reg_state_cr);

        $reg_state = match($this->action->ac_reg_state_cr) {
            'crbr' => 'br',
            'crgpl' => 'gpl',
        };

        // bereits gespeicherter Captain, neuer Captain leer
        if ($this->newCaptain == 0 && $this->captain >0)
        {
            DB::table('action_members')
                ->where('web_id', $this->newCaptain)
                ->update(['group' => 'cr', 'reg_state' => $reg_state]);
            Log::debug("RlCrewEdit update captain to crew");
        }

        // bisher kein Captain, neuen Captain setzen, eventuell erstellen
        if ($this->newCaptain > 0 && $this->captain == 0)
        {
            $exists = DB::table('action_members')
                ->where('web_id', $this->newCaptain)
                ->exists();

            if ($exists) {

                DB::table('action_members')
                    ->where('web_id', $this->newCaptain)
                    ->update(['group' => 'sf', 'reg_state' => 'ang']);
                Log::debug("RlCrewEdit update captain to SF");
            } else {
                DB::table('action_members')->insert([
                    'web_id' => $this->newCaptain,
                    'group' => 'sf',
                    'reg_state' => 'ang',
                    'action_id' => $this->actionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::debug("RlCrewEdit new captain");
            }

        }

        // bisher gespeicherten Captain durch anderen neuen ersetzen, neuen eventuell erstellen
        if ($this->newCaptain > 0 && $this->captain > 0 && $this->captain != $this->newCaptain)
        {
            DB::table('action_members')
                ->where('web_id', $this->captain)
                ->update(['group' => 'cr', 'reg_state' => $reg_state]);
            Log::debug("RlCrewEdit update old captain to CR");

            $exists = DB::table('action_members')
                ->where('web_id', $this->newCaptain)
                ->exists();

            if ($exists) {

                DB::table('action_members')
                    ->where('web_id', $this->newCaptain)
                    ->update(['group' => 'sf', 'reg_state' => 'ang']);
                Log::debug("RlCrewEdit update captain to SF");
            } else {
                DB::table('action_members')->insert([
                    'web_id' => $this->newCaptain,
                    'group' => 'sf',
                    'reg_state' => 'ang',
                    'action_id' => $this->actionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::debug("RlCrewEdit new captain");
            }
        }

        // wenn bisher gespeicherter Captain und neuer Captain gleich sind, passiert nichts

    }

    /* **************************************
     *    update($property)
     ****************************************/
    public function updated($property): void
    {
        Log::debug("--- RlCrewEdit.updatedNewCaptain -----------------------------");
        if ($property == 'newCaptain') {
            $this->newCaptainName = $this->captains->firstWhere('webid', $this->newCaptain)->display_name;
        }

    }

    /* **************************************
     *    saveCrew()
     ****************************************/
    public function saveCrew(): void
    {
        Log::debug("--- RlCrewEdit.saveCrew ------------------------------");
        Log::debug('crewSelections: ' . print_r($this->crewSelections, true));
        Log::debug('newCrewSelections: ' . print_r($this->newCrewSelections, true));

        foreach ($this->newCrewSelections as $crewId => $reg_state) {

            Log::debug("foreach: ".$crewId.','.$reg_state);
            //DB::table('action_members')
            //    ->where('web_id', $crewId)
            //    ->update(['reg_state' => $reg_state]);
        }
    }

    /* **************************************
     *    render()
     ****************************************/
    public function render(): View
    {
        Log::debug("--- RlCrewEdit.render ----------------------------");
        //DB::enableQueryLog();

        if(!empty($this->actionId)) {
            $this->action = DB::table('actions')
                ->where('id', $this->actionId)
                ->first();
            //Log::debug('action aus DB : '.print_r($this->action, true));
            //$q = ;
            //Log::debug('sql: '.print_r(DB::getQueryLog(), true));

            $this->crew = DB::table('action_members')
                ->join('members', 'action_members.web_id', '=', 'members.webid')
                ->where('action_members.action_id', $this->actionId)
                ->whereLike('action_members.group', '%cr%')
                ->orderBy('created_at')
                ->select('action_members.web_id', 'action_members.created_at', 'action_members.reg_state', DB::raw("
                    CASE
                        WHEN nickname IS NOT NULL AND nickname != '' THEN CONCAT(nickname, ' ', name)
                        ELSE CONCAT(firstname, ' ', name)
                    END AS display_name"))
                ->get();

            Log::debug('crew: '.print_r($this->crew, true));

            foreach ($this->crew as $crew) {
                $this->crewSelections[$crew->web_id] = $crew->reg_state;
            }
            $this->newCrewSelections = $this->crewSelections;
            //Log::debug('sql: '.print_r(DB::getQueryLog(), true));

            $this->service = DB::table('action_members')
                ->join('members', 'action_members.web_id', '=', 'members.webid')
                ->where('action_members.action_id', $this->actionId)
                ->whereLike('action_members.group', '%sv%')
                ->orderBy('created_at')
                ->select('action_members.web_id', 'action_members.created_at', 'action_members.reg_state', DB::raw("
                    CASE
                        WHEN nickname IS NOT NULL AND nickname != '' THEN CONCAT(nickname, ' ', name)
                        ELSE CONCAT(firstname, ' ', name)
                    END AS display_name"))
                ->get();
            Log::debug('service: '.print_r($this->service, true));

            $this->captain = DB::table('action_members')
                ->where('action_members.action_id', $this->actionId)
                ->whereLike('action_members.group', '%sf%')
                ->value('web_id');
            $this->newCaptain = $this->captain;

            $this->captainName = ($this->captain > 0) ? $this->captains->firstWhere('webid', $this->captain)->display_name : '';
            //$this->captainName = $this->captains->firstWhere('webid', $this->captain)->display_name ?? '';
            $this->newCaptainName = $this->captains->firstWhere('webid', $this->newCaptain)->display_name ?? '';
            Log::debug('captain name: '.print_r($this->captainName, true));
            Log::debug('new captain name: '.print_r($this->newCaptainName, true));


        }

        return view('livewire.pages.rl-crew-edit');
    }
}
