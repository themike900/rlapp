<?php

namespace App\Livewire;

use App\Models\Action;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ActionsTable extends Component
{
    public $filter = 'of,gs';

    public function updated($propertyName): void
    {
        Log::info($this->$propertyName);
    }

    public function render(): Application|Factory|View|\Illuminate\View\View
    {
        Log::debug('Filter: '.$this->filter);

        $actions = Action::query()
            ->whereIn('action_state_sc', explode(',',$this->filter))
            ->orderBy('action_date')
            ->get();

        return view('livewire.actions-table', compact('actions'));
    }
}
