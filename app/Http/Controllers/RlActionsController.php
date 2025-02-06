<?php

namespace App\Http\Controllers;

class RlActionsController extends Controller
{
    public function RlActionList()
    {
        return view('rl-views.rl-actions-list');
    }

    public function RlActionEdit() {
        return view('rl-views.rl-edit-action');
    }

    public function RlActionNew() {
        return view('rl-views.rl-new-action');
    }

    public function RlActionSave() {
        return view('rl-views.rl-edit-action');
    }

}
