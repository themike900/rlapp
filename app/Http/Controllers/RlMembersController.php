<?php

namespace App\Http\Controllers;

class RlMembersController extends Controller
{
    public function RlMembersList()
    {
        return view('rl-views.rl-member-list');
    }

    public function RlMembersEdit() {
        return view('rl-views.rl-edit-member');
    }

    public function RlMembersSave() {
        return view('rl-views.rl-edit-member');
    }

    public function RlMembersImport() {
        return view('rl-views.rl-member-import');
    }

}
