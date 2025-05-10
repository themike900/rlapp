<?php

namespace App\Livewire\Pages;

use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\ActionMember;

#[Layout('layouts.app')]
class RlMemEdit extends Component
{
    public $action = null;
    public $actionId = null;

    public $crew = null;
    public $crewSelections = [];
    public $newCrewSelections = [];

    public $service = null;
    public $serviceSelections = [];
    public $newServiceSelections = [];

    public $teilnehmer = null;
    public $teilnehmerSelections = [];
    public $newTeilnehmerSelections = [];

    public $wlist = null;
    public $wlistSelections = [];
    public $newWlistSelections = [];

    public $guests = null;
    public $guestSelections = [];
    public $newGuestSelections = [];

    public $captain = 0;
    public $captainName = '';
    public $newCaptain = 0;
    public $newCaptainName = '';
    public $selectActions = null;
    public $captains;

    public $suchErgebnisse = [];
    public $search = '';
    public $show;
    public $members = [];
    public $action_members = [];

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
            ->whereIn('action_type_sc', ['vf', 'af', 'bf', 'vt','sc','mv','vr','afr','abr','wa'])
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
     *    saveTeilnehmer()
     ****************************************/
    public function saveTeilnehmer(): void
    {
        Log::debug("--- RlCrewEdit.saveTeilnehmer ------------------------------");
        Log::debug('teilnehmerSelections: ' . print_r($this->teilnehmerSelections, true));
        Log::debug('newTeilnehmerSelections: ' . print_r($this->newTeilnehmerSelections, true));

        foreach ($this->newTeilnehmerSelections as $web_id => $group) {

            //Log::debug("foreach: ".$web_id.','.$reg_state);

            if ( $group != $this->teilnehmerSelections[$web_id]) {
                Log::debug("foreach change: ".$web_id.','.$group);

                $reg_state = match($group) {
                    'cr', 'sv' => 'br',
                    'wl' => 'wl',
                    'tn' => 'ang',
                    default => ''
                };
                $group = ($group == 'wl') ? 'tn' : $group;
                Log::debug("foreach change: ".$web_id.','.$group.','.$reg_state);

                if ($group == 'del') {
                    ActionMember::deleteRecord($this->actionId, $web_id);
                } else {
                    ActionMember::updateRecord($this->actionId, $web_id,['reg_state' => $reg_state, 'group' => $group]);
                }

            }
        }

        DB::table('actions')
            ->where('id', $this->actionId)
            ->update(['ac_reg_state_cr' => 'crgpl']);
    }

    /* **************************************
     *    saveWarteliste()
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

    public function updatedSearch(): void
    {
        Log::debug('updatedSearch: '.$this->search.' '.'%' . $this->search . '%');
        //$this->suchErgebnisse = DB::table('members')
        //    ->where('firstname', 'like', '%' . $this->search . '%')
        //    ->get();
        $this->suchErgebnisse = Member::query()
            ->when($this->search, fn($query) => $query->where('firstname', 'like', "%{$this->search}%"))
            ->orderBy('firstname')
            ->get();
        Log::debug(count($this->suchErgebnisse));

    }
    public function addMember($memberId,$group,$state=null): void
    {
        Log::debug('addMember: '.$memberId);
        $member = DB::table('members')->find($memberId);
        if ($member) {

            // fehlende webid ergänzen
            if (empty($member->webid)) {
                $max_webid = DB::table('members')->max('webid');
                $member->webid = $max_webid + 1;
                DB::table('members')
                    ->where('id', $memberId)
                    ->update(['webid' => $member->webid]);
            }

            // reg_state für Gruppe des Members festlegen
            Log::debug('selectedGroup: ' . $memberId);
            $reg_state = match ($group) {
                'tn' => 'ang',
                'cr' => 'br',
                'sv' => 'br',
                'sf' => 'ang',
            };
            $reg_state = ($state == 'wl') ? 'wl' : $reg_state;


            // Füge Member zu action_members hinzu
            DB::table('action_members')
                ->insert([
                    'created_at' => now(),
                    'updated_at' => now(),
                    'action_id' => $this->actionId,
                    'web_id' => $member->webid,
                    'group' => $group,
                    'reg_state' => $reg_state,
                    'reg_error' => ''
                ]);
        }
        $this->search = '';
        //$this->updatedSearch();

        $this->action_members = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_id', $this->actionId)
            ->orderBy('members.firstname')
            //->select(['action_members.*', 'members.name','members.firstname','members.nickname','members.groups'])
            ->get();

        $this->members = DB::table('members')
            ->leftJoin('action_members', 'members.webid', '=', 'action_members.web_id')
            ->whereNull('action_members.web_id') // Nur Mitglieder, die nicht in action_members sind
            ->get();

    }
    public function close(): void
    {
        $this->dispatch('refreshTable');
        $this->show = false;
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


            $this->action->ac_reg_state_tn_name = match ($this->action->ac_reg_state_tn) {
                'tnon' => ' Teilnehmer offen',
                'tnoff' => 'Teilnehmer voll',
            };
            //Log::debug('action aus DB : '.print_r($this->action, true));
            //$q = ;
            //Log::debug('sql: '.print_r(DB::getQueryLog(), true));

            /*+++++++++++++++++++
             * Teilnehmer
             * +++++++++++++++++*/
            $this->teilnehmer = DB::table('action_members')
                ->join('members', 'action_members.web_id', '=', 'members.webid')
                ->where('action_members.action_id', $this->actionId)
                ->where('action_members.group', 'tn')
                ->where('action_members.reg_state', 'ang')
                ->orderBy('created_at')
                ->select('action_members.web_id', 'action_members.created_at', 'action_members.reg_state', 'action_members.group', 'members.groups',
                    DB::raw("
                    CASE
                        WHEN nickname IS NOT NULL AND nickname != '' THEN CONCAT(nickname, ' ', name)
                        ELSE CONCAT(firstname, ' ', name)
                    END AS display_name"))
                ->get();
            Log::debug('teilnehmer: '.print_r($this->teilnehmer, true));


            foreach ($this->teilnehmer as $tn) {

                $this->teilnehmerSelections[$tn->web_id] = 'tn';
                $tn->count = DB::table('action_members')
                    ->where('web_id', $tn->web_id)
                    ->whereYear('created_at', now()->year)
                    ->count('id');
            }
            $this->newTeilnehmerSelections = $this->teilnehmerSelections;

            Log::debug('teilnehmer: '.print_r($this->teilnehmer, true));
            //Log::debug('sql: '.print_r(DB::getQueryLog(), true));

            /********************
             * Warteliste
             *********************/
            $this->wlist = DB::table('action_members')
                ->join('members', 'action_members.web_id', '=', 'members.webid')
                ->where('action_members.action_id', $this->actionId)
                ->where('action_members.group', 'tn')
                ->where('action_members.reg_state', 'wl')
                ->orderBy('created_at')
                ->select('action_members.web_id', 'action_members.created_at', 'action_members.reg_state', 'action_members.group', 'members.groups',
                    DB::raw("
                    CASE
                        WHEN nickname IS NOT NULL AND nickname != '' THEN CONCAT(nickname, ' ', name)
                        ELSE CONCAT(firstname, ' ', name)
                    END AS display_name"))
                ->get();

            Log::debug('warteliste: '.print_r($this->wlist, true));

            foreach ($this->wlist as $wl ) {
                $this->wlistSelections[$wl->web_id] = 'wl';
            }
            $this->newWlistSelections = $this->wlistSelections;

            /***********************
             * Guests
             ***********************/
            $this->guests = DB::table('guests')
                ->join('action_members', 'action_members.id', '=', 'guests.reg_id')
                ->join('members', 'action_members.web_id', '=', 'members.webid')
                ->where('guests.gst_action_id', $this->actionId)
                ->orderBy('guests.created_at')
                ->select('guests.id','guests.reg_id', 'members.webid','guests.gst_state', 'guests.name as gst_name', 'members.name', 'members.firstname')
                ->get();

            Log::debug('guests: '.print_r($this->wlist, true));

            foreach ($this->guests as $gst ) {
                $this->guestSelections[$gst->id] = $gst->gst_state;
            }
            $this->newGuestSelections = $this->guestSelections;


        }

        return view('livewire.pages.rl-mem-edit');
    }
}
