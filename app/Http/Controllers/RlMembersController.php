<?php

namespace App\Http\Controllers;

class RlMembersController extends Controller
{
    public function RlMembersList()
    {
        return view('rl-views.rl-actions-list');
    }

    public function RlMembersEdit() {
        return view('rl-views.rl-edit-action');
    }

    public function RlMembersSave() {
        return view('rl-views.rl-edit-action');
    }

}
