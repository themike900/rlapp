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
     * Liste der Aktivitäten anzeigen
     */
    public function index(): View
    {
        return view('actions.index', [
            'actions' => Action::orderBy('action_date')->get(),
        ]);
    }

    /**
     * Anzeigen des Erstellungsformulars für eine Aktivität
     */
    public function create(): View
    {
        return view('actions.create');
    }

    /**
     * Speichern einer neuen Aktivität
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
     * Detailansicht einer Aktivität
     */
    public function show(Action $action): View
    {
        return view('actions.show', [ 'action' => $action ]);
    }

    /**
     * Aktivität für die Bearbeitung laden
     */
    public function edit(Action $action)
    {
        //
    }

    /**
     * Änderungen an der Aktivität speichern
     */
    public function update(Request $request, Action $action)
    {
        //
    }

    /**
     * Aktivität löschen
     */
    public function destroy(Action $action)
    {
        //
    }
}
