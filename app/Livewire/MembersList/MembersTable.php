<?php

namespace App\Livewire\MembersList;

use App\Models\Member;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class MembersTable extends Component
{
    public $search = '';
    public $filter = '';
    public $orderFirstname = 'asc';
    public $orderLastname = '';
    public $orderLastAccess = '';
    public $orderFahrten = '';
    public $orderIds = '';

    public $members = [];
    public $countMembers = 0;

    public function updated($propertyName): void
    {
        Log::info($this->$propertyName);
    }

    public function sortBy($column): void
    {

        if ($column == 'firstname') {
            $this->orderFirstname = match ($this->orderFirstname) {
                'asc' => 'desc',
                'desc','' => 'asc',
            };
            $this->orderLastname = '';
            $this->orderLastAccess = '';
            $this->orderFahrten ='';
            $this->orderIds = '';
        }
        if ($column == 'lastname') {
            $this->orderLastname = match ($this->orderLastname) {
                'asc' => 'desc',
                'desc','' => 'asc',
            };
            $this->orderFirstname = '';
            $this->orderLastAccess = '';
            $this->orderFahrten ='';
            $this->orderIds = '';
        }
        if ($column == 'lastAccess') {
            $this->orderLastAccess = match ($this->orderLastAccess) {
                'asc','' => 'desc',
                'desc' => 'asc',
            };
            $this->orderFirstname = '';
            $this->orderLastname = '';
            $this->orderFahrten ='';
            $this->orderIds = '';
        }
        if ($column == 'fahrten') {
            $this->orderFahrten = match ($this->orderFahrten) {
                '', 'countTn' => 'countCr',
                'countCr' => 'countSv',
                'countSv' => 'countSf',
                'countSf' => 'countTn',
            };
            $this->orderFirstname = '';
            $this->orderLastname = '';
            $this->orderLastAccess = '';
            $this->orderIds = '';
        }
        if ($column == 'ids') {
            $this->orderIds = match ($this->orderIds) {
                'mv_id','' => 'id',
                'id' => 'webid',
                'webid' => 'mv_id',
            };
            $this->orderFirstname = '';
            $this->orderLastname = '';
            $this->orderFahrten ='';
            $this->orderLastAccess = '';
        }

    }

    public function render(): Application|Factory|View|\Illuminate\View\View
    {
        Log::debug('Search: '.$this->search);
        Log::debug('Filter: '.$this->filter);

        $orderByPrim = 'firstname';
        $orderPrim = 'asc';
        $orderBySec = 'firstname';
        $orderSec = 'asc';

        if ($this->orderFirstname != '') {
            $orderPrim = $this->orderFirstname;
            $orderBySec = 'name';
        }
        if ($this->orderLastname != '') {
            $orderByPrim = 'name';
            $orderPrim = $this->orderLastname;
        }
        if ($this->orderLastAccess != '') {
            $orderByPrim = 'last_access';
            $orderPrim = $this->orderLastAccess;
        }
        if ($this->orderIds != '') {
            $orderByPrim = $this->orderIds;
        }

        $members = collect(
            Member::query()
                ->when($this->search, fn($query) => $query->where('firstname', 'like', "%{$this->search}%"))
                ->when($this->filter, fn($query) => $query->where('groups', 'like', "%{$this->filter}%"))
                ->whereNot('firstname','-')
                ->orderBy($orderByPrim, $orderPrim)
                ->orderBy($orderBySec, $orderSec)
                ->get()
        );
        $this->countMembers = $members->count();

        foreach ($members as $member) {

            $member->countTn = DB::table('action_members')
                ->join('actions', 'actions.id', '=', 'action_members.action_id')
                ->where('web_id', '=', $member->webid)
                ->whereYear('actions.action_date', '=', now()->year)
                ->where('action_members.group', '=','tn')
                ->whereIn('actions.action_type_sc', ['vf','gfx','uf','af'])
                ->count();

            $member->countCr = DB::table('action_members')
                ->join('actions', 'actions.id', '=', 'action_members.action_id')
                ->where('web_id', '=', $member->webid)
                ->whereYear('actions.action_date', '=', now()->year)
                ->where('action_members.group', '=','cr')
                ->where('action_members.reg_state', '=','gpl')
                ->count() ;

            $member->countSv = DB::table('action_members')
                ->join('actions', 'actions.id', '=', 'action_members.action_id')
                ->where('web_id', '=', $member->webid)
                ->whereYear('actions.action_date', '=', now()->year)
                ->where('action_members.group', '=','sv')
                ->where('action_members.reg_state', '=','gpl')
                ->count();

            $member->countSf = DB::table('action_members')
                ->join('actions', 'actions.id', '=', 'action_members.action_id')
                ->where('web_id', '=', $member->webid)
                ->whereYear('actions.action_date', '=', now()->year)
                ->where('action_members.group', '=','sf')
                ->count();

            $rand = mt_rand(1000000000,9999999999);
            $member->webIdEnc = base64_encode( $member->webid . '/' . $rand);

        }

        if ($this->orderFahrten != '') {
            $members = $members->sortBy($this->orderFahrten,SORT_REGULAR,true);
            //Log::debug("orderFahrten: $this->orderFahrten");
        }

        $this->members = $members;



        //return view('livewire.members-list.members-table', compact('members'));
        return view('livewire.members-list.members-table');
    }
}
