<?php

namespace App\Livewire\Pages;

use App\Jobs\SendEmail;
use App\Models\Member;
use App\Services\ParticipantsCalcService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\ActionMember;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

#[Layout('layouts.app')]
class RlCrewEdit extends Component
{
    public $action = null;
    public $actionId = null;

    // Crew --------------------------
    public $crew = null;
    public $crewSelections = [];
    public $newCrewSelections = [];
    public $savedCrew = false;
    public $sentEmailsCrew = false;
    public $closedCrew = false;
    public $crewEmailsCount = 0;
    public $crewEmailsSent = 0;
    public $crewCount = 0;
    public $crewCloseBtn = false;

    // Service -----------------------
    public $service = null;
    public $serviceSelections = [];
    public $newServiceSelections = [];
    public $savedService = false;
    public $sentEmailsService = false;
    public $closedService = false;
    public $serviceEmailsCount = 0;
    public $serviceEmailsSent = 0;
    public $serviceCount = 0;
    public $serviceCloseBtn = false;

    // captain -----------------------
    public $captain = 0;
    public $captainName = '';
    public $newCaptain = 0;
    public $newCaptainName = '';
    public $selectActions = null;
    public $captains;
    public $savedCaptain = false;

    // $cnt array
    public $cnt = [];

    // Suche für Hinzufügen
    public $suchErgebnisse = [];
    public $search = '';
    public $show;
    public $members = [];
    public $action_members = [];

    /**
     * mount($actionId)
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
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
        //Log::debug('actions für select aus DB : '.print_r($this->selectActions, true));
        $this->actionId = (empty($this->actionId)) ? $this->selectActions[0]->id : $this->actionId;

        $this->captains = DB::table('members')
            ->whereLike('groups', '%sf%')
            ->orderBy('firstname')
            ->select('webid', 'firstname', 'name', 'nickname', 'fullname')
            ->get();
        //Log::debug('captains für select aus DB : '.print_r($this->captains, true));

        $this->savedCrew = false;
        $this->sentEmailsCrew = false;
        $this->closedCrew = false;
        $this->savedService = false;
        $this->sentEmailsService = false;
        $this->closedService = false;
        $this->savedCaptain = false;

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
            Log::debug("RlCrewEdit update captain $this->captain to cr $reg_state");
            dispatch(new SendEmail($this->captain, 'sf-absage', ['action_id' => $this->actionId]));
            $this->savedCaptain = true;

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
                ActionMember::updateRecord($this->actionId, $this->newCaptain,[
                    'group' => 'sf',
                    'reg_state' => 'ang',
                ]);
                Log::debug("RlCrewEdit update captain $this->newCaptain to sf ang");
            } else {
                // wenn nicht existiert, neu mit sf, ang
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
            dispatch(new SendEmail($this->newCaptain, 'sf-zusage', ['action_id' => $this->actionId]));
            $this->savedCaptain = true;

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
            dispatch(new SendEmail($this->captain, 'sf-absage', ['action_id' => $this->actionId]));

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
                ActionMember::updateRecord($this->actionId, $this->newCaptain,[
                    'group' => 'sf',
                    'reg_state' => 'ang',
                ]);
                Log::debug("RlCrewEdit update captain $this->newCaptain to sf ang");
            } else {
                // wenn nicht existiert, neu mit sf, ang
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
            dispatch(new SendEmail($this->newCaptain, 'sf-zusage', ['action_id' => $this->actionId]));
            $this->savedCaptain = true;
        }

        // wenn bisher gespeicherter Captain und neuer Captain gleich sind, passiert nichts

        $this->savedCrew = false;
        $this->sentEmailsCrew = false;
        $this->closedCrew = false;
        $this->savedService = false;
        $this->sentEmailsService = false;
    }

    /* **************************************
     *    update($property)
     ****************************************/
    public function updated($property): void
    {
        Log::debug("--- RlCrewEdit.updated property: $property -----------------------------");

        if ($property == 'newCaptain') {
            $this->newCaptainName = ($this->newCaptain > 0) ? $this->captains->firstWhere('webid', $this->newCaptain)->fullname : '';
        }

        if ($property == 'actionId') {
            session()->put('actionID', $this->actionId);

            $this->savedCrew = false;
            $this->sentEmailsCrew = false;
            $this->closedCrew = false;
            $this->savedService = false;
            $this->sentEmailsService = false;
            $this->savedCaptain = false;
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

                if ($reg_state == 'gpl') {
                    ActionMember::updateRecord($this->actionId, $web_id,[
                        'reg_state' => $reg_state,
                        'reg_email' => 'crew-zusage'
                    ]);
                } elseif ($reg_state == 'abgl') {
                    ActionMember::updateRecord($this->actionId, $web_id,[
                        'reg_state' => $reg_state,
                        'reg_email' => 'crew-absage'
                    ]);
                } else {
                    ActionMember::updateRecord($this->actionId, $web_id,[
                        'reg_state' => $reg_state,
                        'reg_email' => ''
                    ]);
                }

            }
        }

        $this->savedCrew = true;
        $this->sentEmailsCrew = false;
        $this->closedCrew = false;
        $this->savedService = false;
        $this->sentEmailsService = false;
        $this->closedService = false;
        $this->savedCaptain = false;
    }

    /* **************************************
    *    sendEmailsCrew()
    ****************************************/
    public function sendEmailsCrew(): void
    {
        Log::debug("--- RlCrewEdit.sendEmailsCrew ------------------------------");

        $regs = DB::table('action_members')
            ->where('action_id', $this->actionId)
            ->whereNot('reg_email', '')
            ->get();
        Log::debug('regs: ' . print_r($regs, true));

        $this->crewEmailsSent = 0;
        foreach ($regs as $reg) {
            dispatch(new SendEmail($reg->web_id, $reg->reg_email, ['action_id' => $this->actionId]));
            ActionMember::updateRecord($this->actionId, $reg->web_id,[
                'reg_email' => ''
            ]);
            $this->crewEmailsSent++;
            Log::debug("SendEmail: $reg->web_id, $reg->reg_email, $this->actionId");
        }

        $this->crewEmailsCount = 0;

        $this->savedCrew = false;
        $this->sentEmailsCrew = true;
        $this->closedCrew = false;
        $this->savedService = false;
        $this->sentEmailsService = false;
        $this->savedCaptain = false;
    }

    /* **************************************
    *    closeCrew()
    ****************************************/
    public function closeCrew(): void
    {
        Log::debug("--- RlCrewEdit.closeCrew ------------------------------");

        DB::table('actions')
            ->where('id', $this->actionId)
            ->update(['ac_reg_state_cr' => 'crgpl']);

        $this->savedCrew = false;
        $this->sentEmailsCrew = false;
        $this->closedCrew = true;
        $this->savedService = false;
        $this->sentEmailsService = false;
        $this->closedService = false;
        $this->savedCaptain = false;
    }

    /* **************************************
     *    saveService()
     ****************************************/
    public function saveService(): void
    {
        Log::debug("--- RlCrewEdit.saveService ------------------------------");
        //Log::debug('serviceSelections: ' . print_r($this->serviceSelections, true));
        //Log::debug('newServiceSelections: ' . print_r($this->newServiceSelections, true));

        foreach ($this->newServiceSelections as $web_id => $reg_state) {

            //Log::debug("foreach: ".$web_id.','.$reg_state);

            if ( $reg_state != $this->serviceSelections[$web_id]) {
                Log::debug("foreach change: ".$web_id.','.$reg_state);

                if ($reg_state == 'gpl') {
                    ActionMember::updateRecord($this->actionId, $web_id,[
                        'reg_state' => $reg_state,
                        'reg_email' => 'service-zusage'
                    ]);
                } elseif ($reg_state == 'abgl') {
                    ActionMember::updateRecord($this->actionId, $web_id,[
                        'reg_state' => $reg_state,
                        'reg_email' => 'service-absage'
                    ]);
                } else {
                    ActionMember::updateRecord($this->actionId, $web_id, [
                        'reg_state' => $reg_state,
                        'reg_email' => ''
                    ]);
                }
            }
        }

        $this->savedCrew = false;
        $this->sentEmailsCrew = false;
        $this->closedCrew = false;
        $this->savedService = true;
        $this->sentEmailsService = false;
        $this->closedService = false;
        $this->savedCaptain = false;
    }

    /* **************************************
    *    sendEmailsService()
    ****************************************/
    public function sendEmailsService(): void
    {
        Log::debug("--- RlCrewEdit.sendEmailsService ------------------------------");

        $regs = DB::table('action_members')
            ->where('action_id', $this->actionId)
            ->whereNot('reg_email', '')
            ->get();
        Log::debug('regs: ' . print_r($regs, true));

        $this->serviceEmailsSent = 0;
        foreach ($regs as $reg) {
            dispatch(new SendEmail($reg->web_id, $reg->reg_email, ['action_id' => $this->actionId]));
            ActionMember::updateRecord($this->actionId, $reg->web_id,[
                'reg_email' => ''
            ]);
            $this->serviceEmailsSent++;
            Log::debug("SendEmail: $reg->web_id, $reg->reg_email, $this->actionId");
        }

        $this->serviceEmailsCount = 0;

        $this->savedCrew = false;
        $this->sentEmailsCrew = false;
        $this->closedCrew = false;
        $this->savedService = false;
        $this->sentEmailsService = true;
        $this->closedService = false;
        $this->savedCaptain = false;
    }

    /* **************************************
    *    closeService()
    ****************************************/
    public function closeService(): void
    {
        Log::debug("--- RlCrewEdit.closeService ------------------------------");

        DB::table('actions')
            ->where('id', $this->actionId)
            ->update(['ac_reg_state_sv' => 'svgpl']);

        $this->savedCrew = false;
        $this->sentEmailsCrew = false;
        $this->closedCrew = false;
        $this->savedService = false;
        $this->sentEmailsService = false;
        $this->closedService = true;
        $this->savedCaptain = false;
    }

    /* **************************************
    *    updatedSearch()
    ****************************************/
    public function updatedSearch(): void
    {
        Log::debug('--- RlCrewEdit.updatedSearch: '.$this->search.' '.'%' . $this->search . '%');
        //$this->suchErgebnisse = DB::table('members')
        //    ->where('firstname', 'like', '%' . $this->search . '%')
        //    ->get();
        $this->suchErgebnisse = Member::query()
            ->when($this->search, fn($query) => $query->where('firstname', 'like', "%$this->search%"))
            ->whereNot('firstname','-')
            ->orderBy('firstname')
            ->get();
        Log::debug(count($this->suchErgebnisse));

        $this->savedCrew = false;
        $this->sentEmailsCrew = false;
        $this->closedCrew = false;
        $this->savedService = false;
        $this->sentEmailsService = false;
        $this->savedCaptain = false;
    }

    /* **************************************
    *    addCrew()
    ****************************************/
    public function addCrew($memberId,$group,$state=null): void
    {
        Log::debug("--- RlCrewEdit.addCrew: $memberId ----------------------------");
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
            Log::debug('selectedGroup: ' . $group);
            $reg_state = match ($group) {
                'tn', 'sf' => 'ang',
                'cr', 'sv' => 'br',
            };
            $reg_state = ($state == 'wl') ? 'wl' : $reg_state;

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
                        'reg_state' => $reg_state,
                        'reg_error' => ''
                    ]);
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

        $this->members = [];

        $this->show = false;

        $this->savedCrew = false;
        $this->sentEmailsCrew = false;
        $this->closedCrew = false;
        $this->savedService = false;
        $this->sentEmailsService = false;
        $this->savedCaptain = false;
    }

    /* **************************************
    *    close()
    ****************************************/
    public function close(): void
    {
        Log::debug("--- RlCrewEdit.close ------------------------------");

        $this->dispatch('refreshTable');
        $this->show = false;

    }

    /* **************************************
    *    deleteMessages()
    ****************************************/
    /*public function deleteMessages(): void
    {
        Log::debug("--- RlCrewEdit.deleteMessages ------------------------------");

        $this->savedCrew = false;
        $this->closedCrew = false;
        $this->savedService = false;
        $this->closedService = false;

    }*/

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
                default => ''
            };
            $this->action->ac_reg_state_sv_name = match ($this->action->ac_reg_state_sv) {
                'svbr' => 'offen',
                'svgpl' => 'abgeschlossen',
                default => '',
            };
            //Log::debug('action aus DB : '.print_r($this->action, true));
            //$q = ;
            //Log::debug('sql: '.print_r(DB::getQueryLog(), true));

            // ------ Crew --------------------------------------------------------------
            $this->crew = DB::table('action_members')
                ->join('members', 'action_members.web_id', '=', 'members.webid')
                ->where('action_members.action_id', $this->actionId)
                ->whereLike('action_members.group', '%cr%')
                ->orderBy('created_at')
                ->select('action_members.web_id',
                    'action_members.created_at',
                    'action_members.reg_state',
                    'action_members.group',
                    'members.groups',
                    'members.fullname')
                ->get();
            $this->crewCount = count($this->crew);

            $crewGpl = DB::table('action_members')
                ->where('action_id', $this->actionId)
                ->where('group', 'cr')
                ->where('reg_state', 'gpl')
                ->count();
            $crewBr = DB::table('action_members')
                ->where('action_id', $this->actionId)
                ->whereLike('group', '%cr%')
                ->where('reg_state', 'br')
                ->count();
            $this->crewCloseBtn = ($crewGpl >= 5 && $crewBr == 0);

            //Log::debug('crew: '.print_r($this->crew, true));

            foreach ($this->crew as $crew) {

                $this->crewSelections[$crew->web_id] = $crew->reg_state;
                $crew->count = DB::table('action_members')
                    ->where('web_id', $crew->web_id)
                    ->whereYear('created_at', now()->year)
                    ->where('group', 'cr')
                    ->where('reg_state', 'gpl')
                    ->count('id');

                if (str_contains($crew->groups, 'cr')){
                    $crew->groupName = 'Crew';
                } elseif (str_contains($crew->groups, 'tr')){
                    $crew->groupName = 'Trainee';
                } else {
                    $crew->groupName = '';
                }
            }
            $this->newCrewSelections = $this->crewSelections;
            //Log::debug('crew: '.print_r($this->crew, true));
            //Log::debug('sql: '.print_r(DB::getQueryLog(), true));
            $this->crewEmailsCount = DB::table('action_members')
                ->where('action_id', $this->actionId)
                ->whereLike('group', '%cr%')
                ->whereNot('reg_email', '')
                ->count();
            Log::debug("crewEmailsCount: $this->crewEmailsCount");

            // ------ Service --------------------------------------------------------------
            $this->service = DB::table('action_members')
                ->join('members', 'action_members.web_id', '=', 'members.webid')
                ->where('action_members.action_id', $this->actionId)
                ->whereLike('action_members.group', '%sv%')
                ->orderBy('created_at')
                ->select('action_members.web_id',
                    'action_members.created_at',
                    'action_members.reg_state',
                    'members.fullname')
                ->get();
            $this->serviceCount = count($this->service);

            $serviceGpl = DB::table('action_members')
                ->where('action_id', $this->actionId)
                ->where('group', 'sv')
                ->where('reg_state', 'gpl')
                ->count();
            $serviceBr = DB::table('action_members')
                ->where('action_id', $this->actionId)
                ->whereLike('group', '%sv%')
                ->where('reg_state', 'br')
                ->count();
            $this->serviceCloseBtn = ($serviceGpl >= 1 && $serviceBr == 0);

            //Log::debug('service: '.print_r($this->service, true));
            foreach ($this->service as $service) {
                $this->serviceSelections[$service->web_id] = $service->reg_state;
                $service->count = DB::table('action_members')
                    ->where('web_id', $service->web_id)
                    ->whereYear('created_at', now()->year)
                    ->where('group', 'sv')
                    ->where('reg_state', 'gpl')
                    ->count('id');
            }
            $this->newServiceSelections = $this->serviceSelections;
            $this->serviceEmailsCount = DB::table('action_members')
                ->where('action_id', $this->actionId)
                ->where('group', 'sv')
                ->whereNot('reg_email', '')
                ->count();
            Log::debug("serviceEmailsCount: $this->serviceEmailsCount");

            // ------ Schiffsführer --------------------------------------------------------------
            $this->captain = DB::table('action_members')
                ->where('action_members.action_id', $this->actionId)
                ->whereLike('action_members.group', '%sf%')
                ->value('web_id');
            $this->newCaptain = $this->captain;

            $this->captainName = ($this->captain > 0) ? $this->captains->firstWhere('webid', $this->captain)->fullname : '';
            //$this->captainName = $this->captains->firstWhere('webid', $this->captain)->display_name ?? '';
            $this->newCaptainName = $this->captains->firstWhere('webid', $this->newCaptain)->fullname ?? '';
            //Log::debug('captain name: '.print_r($this->captainName, true));
            //Log::debug('new captain name: '.print_r($this->newCaptainName, true));

            $this->cnt = (new ParticipantsCalcService())->counts($this->actionId);


        }

        return view('livewire.pages.rl-crew-edit');
    }
}
