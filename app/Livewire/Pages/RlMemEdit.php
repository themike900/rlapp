<?php

namespace App\Livewire\Pages;

use App\Jobs\SendEmail;
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

    public $teilnehmer = null;
    public $teilnehmerSelections = [];
    public $newTeilnehmerSelections = [];

    public $wlist = null;
    public $wlistSelections = [];
    public $newWlistSelections = [];

    public $guests = null;
    public $guestSelections = [];
    public $newGuestSelections = [];
    public $guestsEmailsCount = 0;
    public $guestsEmailsSent = 0;
    public $sentEmailsGuests = false;

    public $selectActions = null;

    public $suchErgebnisse = [];
    public $search = '';
    public $show;
    public $members = [];
    public $action_members = [];

    public $savedTn = false;
    public $savedWlist = false;
    public $savedGuests = false;

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

    }


    /* **************************************
     *    update($property)
     ****************************************/
    public function updated($property): void
    {
        if ($property == 'actionId') {
            session()->put('actionID', $this->actionId);
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
                } elseif ($group != $this->teilnehmerSelections[$web_id]) {
                    ActionMember::updateRecord($this->actionId, $web_id,['reg_state' => $reg_state, 'group' => $group]);
                }

            }
        }
        $this->savedTn = true;
        $this->savedWlist = false;
        $this->savedGuests = false;
        $this->sentEmailsGuests = false;

    }

    /* **************************************
     *    saveWarteliste()
     ****************************************/
    public function saveWarteliste(): void
    {
        Log::debug("--- RlCrewEdit.saveWarteliste ------------------------------");
        Log::debug('wlistSelections: ' . print_r($this->wlistSelections, true));
        Log::debug('newWlistSelections: ' . print_r($this->newWlistSelections, true));

        foreach ($this->newWlistSelections as $web_id => $group) {

            Log::debug("foreach: ".$web_id.','.$group);
            $reg_state = match($group) {
                'tn' => 'ang',
                'cr' => 'br',
                default => ''
            };
            //$group = ($group == 'wl') ? 'tn' : $group;

            if ($group == 'del') {
                ActionMember::deleteRecord($this->actionId, $web_id);
                dispatch(new SendEmail($web_id, 'del_tn_wlist', ['action_id' => $this->actionId]));
            } elseif ( $group != $this->wlistSelections[$web_id]) {
                ActionMember::updateRecord($this->actionId, $web_id,['reg_state' => $reg_state, 'group' => $group]);
                if ($group == 'cr') {
                    dispatch(new SendEmail($web_id, 'wl-to-crew', ['action_id' => $this->actionId]));
                }
                if ($group == 'tn') {
                    dispatch(new SendEmail($web_id, 'wl-to-tn', ['action_id' => $this->actionId]));
                }
            }
        }
        $this->savedTn = false;
        $this->savedWlist = true;
        $this->savedGuests = false;
        $this->sentEmailsGuests = false;

    }

    /* **************************************
     *    saveGuests()
     ****************************************/
    public function saveGuests(): void
    {
        Log::debug("--- RlCrewEdit.saveGuests ------------------------------");
        Log::debug('guestSelections: ' . print_r($this->guestSelections, true));
        Log::debug('newGuestSelections: ' . print_r($this->newGuestSelections, true));

        foreach ($this->newGuestSelections as $gst_id => $state) {

            if ($state != $this->guestSelections[$gst_id]) {
                Log::debug("update: ".$gst_id.','.$state);

                $gst_email = match($state) {
                    'abgelehnt' => 'gst-absage',
                    'angenommen' => 'gst-zusage',
                    default => ''
                };

                DB::table('guests')
                    ->where(['id' => $gst_id])
                    ->update([
                        'gst_state' => $state,
                        'gst_email' => $gst_email,
                    ]);


            }

        }

        $this->savedTn = false;
        $this->savedWlist = false;
        $this->savedGuests = true;
        $this->sentEmailsGuests = false;

    }

    /* **************************************
    *    sendEmailsGuests()
    ****************************************/
    public function sendEmailsGuests(): void
    {
        Log::debug("--- RlCrewEdit.sendEmailsGuests ------------------------------");

        $guests = DB::table('guests')
            ->join('action_members', 'guests.reg_id', '=', 'action_members.id')
            ->where('gst_action_id', $this->actionId)
            ->whereNot('gst_email', '')
            ->select('guests.*', 'action_members.web_id')
            ->get();
        Log::debug('guests: ' . print_r($guests, true));

        $this->guestsEmailsSent = 0;
        foreach ($guests as $gst) {
            dispatch(new SendEmail($gst->web_id, $gst->gst_email, ['action_id' => $this->actionId, 'gst_name' => $gst->name]));
            DB::table('guests')
                ->where('id', $gst->id)
                ->update([
                    'gst_email' => '',
                ]);
            //ActionMember::updateRecord($this->actionId, $reg->web_id,['reg_email' => '']);
            $this->guestsEmailsSent++;
            Log::debug("SendEmail: $gst->web_id, $gst->gst_email, $this->actionId");
        }

        $this->guestsEmailsCount = 0;

        $this->savedTn = false;
        $this->savedWlist = false;
        $this->savedGuests = false;
        $this->sentEmailsGuests = true;
    }

    /* **************************************
     *    updatedSearch()
     ****************************************/
    public function updatedSearch(): void
    {
        Log::debug('updatedSearch: '.$this->search.' '.'%' . $this->search . '%');
        //$this->suchErgebnisse = DB::table('members')
        //    ->where('firstname', 'like', '%' . $this->search . '%')
        //    ->get();
        $this->suchErgebnisse = Member::query()
            ->when($this->search, fn($query) => $query->where('firstname', 'like', "%$this->search%"))
            ->whereNot('firstname','-')
            ->orderBy('firstname')
            ->get();
        Log::debug(count($this->suchErgebnisse));

        $this->savedTn = false;
        $this->savedWlist = false;
        $this->savedGuests = false;
        $this->sentEmailsGuests = false;

    }
    /* **************************************
     *    addMember()
     ****************************************/
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

            if ($group == 'tn') {

                $exists = DB::table('action_members')
                    ->where('action_id',$this->actionId)
                    ->where('web_id',$member->webid)
                    ->exists();

                // Füge Member zu action_members hinzu
                if (!$exists) {
                    DB::table('action_members')
                        ->insert([
                            'created_at' => now(),
                            'updated_at' => now(),
                            'action_id' => $this->actionId,
                            'web_id' => $member->webid,
                            'group' => $group,
                            'reg_state' => $state,
                            'reg_error' => ''
                        ]);
                }
            }

            if ($group == 'gst') {

                Log::debug('guest for: '.$memberId);
                /*DB::table('guests')
                    ->insert([
                        'created_at' => now(),
                        'reg_id'
                    ]);*/
            }

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

        $this->show = false;
    }
    /* **************************************
     *    close()
     ****************************************/
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
                ->select('action_members.web_id', 'action_members.created_at', 'action_members.reg_state', 'action_members.group', 'members.groups', 'members.fullname')
                ->get();
            //Log::debug('teilnehmer: '.print_r($this->teilnehmer, true));


            foreach ($this->teilnehmer as $tn) {

                $this->teilnehmerSelections[$tn->web_id] = 'tn';
                $tn->count = DB::table('action_members')
                    ->where('web_id', $tn->web_id)
                    ->whereYear('created_at', now()->year)
                    ->count('id');
            }
            $this->newTeilnehmerSelections = $this->teilnehmerSelections;

            //Log::debug('teilnehmer: '.print_r($this->teilnehmer, true));
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
                ->select('action_members.web_id', 'action_members.created_at', 'action_members.reg_state', 'action_members.group', 'members.groups', 'members.fullname')
                ->get();

            //Log::debug('warteliste: '.print_r($this->wlist, true));

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
                ->select('guests.id','guests.reg_id', 'members.webid','guests.gst_state', 'guests.name as gst_name', 'guests.reference', 'members.fullname')
                ->get();

            Log::debug('guests: '.print_r($this->guests, true));

            foreach ($this->guests as $gst ) {
                $this->guestSelections[$gst->id] = $gst->gst_state;
            }
            $this->newGuestSelections = $this->guestSelections;
            $this->guestsEmailsCount = DB::table('guests')
                ->where('gst_action_id', $this->actionId)
                ->whereNot('gst_email', '')
                ->count();
            Log::debug("guestsEmailsCount: $this->guestsEmailsCount");


        }

        return view('livewire.pages.rl-mem-edit');
    }
}
