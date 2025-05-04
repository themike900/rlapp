<?php

namespace App\Livewire\Pages;

use App\Services\ParticipantsCalcService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\ActionMember;

#[Layout('layouts.app')]
class RlCrewEdit extends Component
{
    public $action = null;
    public $actionId = null;
    public $crew = null;
    public $crewSelections = [];
    public $newCrewSelections = [];
    public $service = null;
    public $serviceSelections = [];
    public $newServiceSelections = [];
    public $captain = 0;
    public $captainName = '';
    public $newCaptain = 0;
    public $newCaptainName = '';
    public $selectActions = null;
    public $captains;
    public $cnt = [];

    /* **************************************
     *    mount($actionId)
     ****************************************/
    public function mount($actionId = null): void
    {
        Log::debug("--- RlCrewEdit.mount ----------------------------");
        Log::debug('actionId: '.$actionId);
        $this->actionId = $actionId;

        if (empty($this->actionId)) {
            // actionId aus session holen, wenn vorhanden
            $this->actionId = session()->get('actionID') ?? 0;
            Log::debug('actionId aus session: '.$this->actionId);
        }

        $this->selectActions = DB::table("actions")
            ->whereIn('action_state_sc', ['of', 'gs'])
            ->whereIn('action_type_sc', ['vf', 'af', 'uf', 'gfx','bf'])
            ->orderBy('action_date')
            ->orderBy('action_start_at')
            ->get(['id', 'action_name', 'action_date', 'action_start_at', 'action_end_at']);
        Log::debug('actions für select aus DB : '.print_r($this->selectActions, true));
        $this->actionId = (empty($this->actionId)) ? $this->selectActions[0]->id : $this->actionId;

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
        Log::debug('actionId: '.$this->actionId);
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
            //ActionMember::updateRecord($this->actionId, $this->captain,['group' => 'cr', 'reg_state' => $reg_state]);
            DB::table('action_members')
                ->where('web_id', $this->captain)
                ->where('action_id', $this->actionId)
                ->delete();
                //->update(['group' => 'cr', 'reg_state' => $reg_state]);
            Log::debug("RlCrewEdit update captain $this->captain to cr $reg_state");
        }

        // bisher kein Captain, neuen Captain setzen, eventuell erstellen
        if ($this->newCaptain > 0 && $this->captain == 0)
        {
            $exists = DB::table('action_members')
                ->where('web_id', $this->newCaptain)
                ->where('action_id', $this->actionId)
                ->exists();
            //$x = ActionMember::existsRecord($this->actionId, $this->newCaptain);
            //Log::debug("RlCrewEdit.exists -$exists- -$x-");

            if ($exists) {
                // wenn existiert, dann update zu sf,ang
                //ActionMember::updateRecord($this->actionId, $this->newCaptain,['group' => 'sf', 'reg_state' => 'ang']);
                DB::table('action_members')
                    ->where('web_id', $this->newCaptain)
                    ->where('action_id', $this->actionId)
                    ->update(['group' => 'sf', 'reg_state' => 'ang']);
                Log::debug("RlCrewEdit update captain $this->newCaptain to sf ang");
            } else {
                // wenn nicht existiert, neu mit sf,ang
                DB::table('action_members')->insert([
                    'web_id' => $this->newCaptain,
                    'group' => 'sf',
                    'reg_state' => 'ang',
                    'action_id' => $this->actionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::debug("RlCrewEdit new captain $this->newCaptain");
            }

        }

        // bisher gespeicherten Captain durch anderen neuen ersetzen, neuen eventuell erstellen
        if ($this->newCaptain > 0 && $this->captain > 0 && $this->captain != $this->newCaptain)
        {
            // bisherigen Captain zu CR machen
            //ActionMember::updateRecord($this->actionId, $this->captain,['group' => 'cr', 'reg_state' => $reg_state]);
            DB::table('action_members')
                ->where('web_id', $this->captain)
                ->where('action_id', $this->actionId)
                ->delete();
                //->update(['group' => 'cr', 'reg_state' => $reg_state]);
            Log::debug("RlCrewEdit update old captain $this->captain to cr $reg_state");

            // gibt es neuen Captain schon?
            $exists = DB::table('action_members')
                ->where('web_id', $this->newCaptain)
                ->where('action_id', $this->actionId)
                ->exists();
            //$x = ActionMember::existsRecord($this->actionId, $this->newCaptain);
            //Log::debug("RlCrewEdit.exists -$exists- -$x-");

            if ($exists) {
                // wenn existiert, dann update zu sf,ang
                //ActionMember::updateRecord($this->actionId, $this->newCaptain,['group' => 'sf', 'reg_state' => 'ang']);
                DB::table('action_members')
                    ->where('web_id', $this->newCaptain)
                    ->where('action_id', $this->actionId)
                    ->update(['group' => 'sf', 'reg_state' => 'ang']);
                Log::debug("RlCrewEdit update captain $this->newCaptain to sf ang");
            } else {
                // wenn nicht existiert, neu mit sf,ang
                DB::table('action_members')->insert([
                    'web_id' => $this->newCaptain,
                    'group' => 'sf',
                    'reg_state' => 'ang',
                    'action_id' => $this->actionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::debug("RlCrewEdit new captain $this->newCaptain");
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
            $this->newCaptainName = ($this->newCaptain > 0) ? $this->captains->firstWhere('webid', $this->newCaptain)->display_name : '';
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

        foreach ($this->newCrewSelections as $web_id => $reg_state) {

            //Log::debug("foreach: ".$web_id.','.$reg_state);

            if ( $reg_state != $this->crewSelections[$web_id]) {
                Log::debug("foreach change: ".$web_id.','.$reg_state);
                ActionMember::updateRecord($this->actionId, $web_id,['reg_state' => $reg_state]);
            }
        }

        DB::table('actions')
            ->where('id', $this->actionId)
            ->update(['ac_reg_state_cr' => 'crgpl']);
    }

    /* **************************************
     *    saveService()
     ****************************************/
    public function saveService(): void
    {
        Log::debug("--- RlCrewEdit.saveService ------------------------------");
        Log::debug('serviceSelections: ' . print_r($this->serviceSelections, true));
        Log::debug('newServiceSelections: ' . print_r($this->newServiceSelections, true));

        foreach ($this->newServiceSelections as $web_id => $reg_state) {

            //Log::debug("foreach: ".$web_id.','.$reg_state);

            if ( $reg_state != $this->serviceSelections[$web_id]) {
                Log::debug("foreach change: ".$web_id.','.$reg_state);
                ActionMember::updateRecord($this->actionId, $web_id,['reg_state' => $reg_state]);
            }
        }

        DB::table('actions')
            ->where('id', $this->actionId)
            ->update(['ac_reg_state_sv' => 'svgpl']);
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

            $this->action->ac_reg_state_cr_name = match ($this->action->ac_reg_state_cr) {
                'crbr' => 'offen',
                'crgpl' => 'abgeschlossen',
            };
            $this->action->ac_reg_state_sv_name = match ($this->action->ac_reg_state_sv) {
                'svbr' => 'offen',
                'svgpl' => 'abgeschlossen',
                default => '',
            };
            //Log::debug('action aus DB : '.print_r($this->action, true));
            //$q = ;
            //Log::debug('sql: '.print_r(DB::getQueryLog(), true));

            $this->crew = DB::table('action_members')
                ->join('members', 'action_members.web_id', '=', 'members.webid')
                ->where('action_members.action_id', $this->actionId)
                ->whereLike('action_members.group', '%cr%')
                ->orderBy('created_at')
                ->select('action_members.web_id', 'action_members.created_at', 'action_members.reg_state', 'action_members.group', 'members.groups',
                    DB::raw("
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
            foreach ($this->service as $service) {
                $this->serviceSelections[$service->web_id] = $service->reg_state;
            }
            $this->newServiceSelections = $this->serviceSelections;

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

            $this->cnt = (new ParticipantsCalcService())->counts($this->actionId);


        }

        return view('livewire.pages.rl-crew-edit');
    }
}
