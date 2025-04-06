<?php

namespace App\Livewire;

use App\Models\Member;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class MembersTable extends Component
{
    public $search = '';
    public $filter = '';

    public function updated($propertyName): void
    {
        Log::info($this->$propertyName);
    }

    public function render(): Application|Factory|View|\Illuminate\View\View
    {
        Log::debug('Search: '.$this->search);
        Log::debug('Filter: '.$this->filter);

        $members = Member::query()
            ->when($this->search, fn($query) => $query->where('firstname', 'like', "%{$this->search}%"))
            ->when($this->filter, fn($query) => $query->where('groups', 'like', "%{$this->filter}%"))
            ->orderBy('firstname')
            ->get();

        return view('livewire.members-table', compact('members'));
    }
}
