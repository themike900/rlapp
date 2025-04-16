<?php

namespace App\Livewire;

use App\Models\Action;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;

class AcMembersModal extends Component
{
    public $show = false;
    public $action_members = [];
    public $actionId;
    public $action;
    public $members = [];
    public $search = '';
    public $save;
    public $suchErgebnisse = [];
    public $selectedGroup;

    #[On('open-ac-members-modal')]
    public function loadData($actionId): void
    {
        $this->search = '';
        $this->selectedGroup = 'cr';
        Log::debug('Loading data: '.$actionId);
        $this->actionId = $actionId;
        $action = Action::find($actionId);
        if ($action) {
            $this->action = $action;
        } else {
            $this->action = [];
        }
        //$this->members = Member::all();
        $this->members = DB::table('members')
            ->leftJoin('action_members', 'members.webid', '=', 'action_members.web_id')
            ->whereNull('action_members.web_id') // Nur Mitglieder, die nicht in action_members sind
            ->get();

        $this->action_members = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_id', $this->actionId)
            ->orderBy('members.firstname')
            ->select(['action_members.*', 'members.name','members.firstname','members.nickname','members.groups'])
            ->get();
        Log::debug($this->action_members);

        $this->show = true;
        //Log::debug('Showing data: '.$this->action["action_name"]);
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

    public function addMember($memberId): void
    {
        Log::debug('addMember: '.$memberId);
        $member = DB::table('members')->find($memberId);
        if ($member) {
            if (empty($member->webid)) {
                $max_webid = DB::table('members')->max('webid');
                $member->webid = $max_webid + 1;
                DB::table('members')
                    ->where('id', $memberId)
                    ->update(['webid' => $member->webid]);
            }

            Log::debug('selectedGroup: '.$memberId);
            $reg_state = match ($this->selectedGroup) {
                'tn' => 'ang',
                'cr' => 'crbr',
                'sv' => 'svbr',
                'sf' => 'ang',
            };

            // Füge Member zu action_members hinzu
            DB::table('action_members')
                ->insert([
                    'created_at' => now(),
                    'updated_at' => now(),
                    'action_id' => $this->actionId,
                    'web_id' => $member->webid,
                    'group' => $this->selectedGroup,
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

    public function render(): View
    {
        //Log::debug($this->suchErgebnisse);
        return view('livewire.ac-members-modal');
    }}
