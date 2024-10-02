<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('actions.index', [
            'actions' => Action::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('actions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request):redirectResponse
    {
        $validated = $request->validate([
            'action_type' => 'required|string|max:50',
        ]);
		
		$action = new Action;
		$action->action_type = $request->action_type;
		$action->action_date = $request->action_date;
		$action->crew_start_at = $request->crew_start_at;
		$action->crew_end_at = $request->crew_end_at;
		$action->action_start_at = $request->action_start_at;
		$action->action_end_at = $request->action_end_at;
 
        $action->save();
 
        return redirect(route('actions.index'));
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Action $action)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Action $action)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Action $action)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Action $action)
    {
        //
    }
}
