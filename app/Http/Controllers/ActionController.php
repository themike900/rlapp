<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use App\Models\Action;
use Illuminate\Http\Request;
//use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;


class ActionController extends Controller
{
    /**
     * Liste der Aktivitäten anzeigen
     */
    public function index(Request $request): View
    {
        //Log::debug($request->input());
        //Log::debug(Session::all());

        // Actions-Filter verarbeiten
        if (!empty($request->input('sel_actions'))) {
            $sel_actions = $request->input('sel_actions');
        } else {
            if (session()->has('sel_actions')) {
                $sel_actions = session('sel_actions');
            } else {
                $sel_actions = ['veranstaltungen', 'ausfahrten'];
            }
        }
        Session::put('sel_actions', $sel_actions);
        $actions_filter = [];
        if (in_array('veranstaltungen', $sel_actions)) {
            $actions_filter = array_merge($actions_filter, ['vt', 'sc']);
        }
        if (in_array('ausfahrten', $sel_actions)) {
            $actions_filter = array_merge($actions_filter, ['vf', 'gf', 'af', 'uf']);
        }

        // Status-Filter verarbeiten
        if (!empty($request->input('sel_states'))) {
            $sel_states = $request->input('sel_states');
        } else {
            if (session()->has('sel_states')) {
                $sel_states = session('sel_states');
                //Log::debug($sel_actions);
            } else {
                $sel_states = ['sichtbar', 'geplant'];
            }
        }
        Session::put('sel_states', $sel_states);
        $states_filter = [];
        if (in_array('sichtbar', $sel_states)) {
            $states_filter = array_merge($states_filter, ['of', 'gs']);
        }
        if (in_array('geplant', $sel_states)) {
            $states_filter = array_merge($states_filter, ['iv', 'br']);
        }
        if (in_array('abgeschlossen', $sel_states)) {
            $states_filter = array_merge($states_filter, ['df', 'as', 'ag']);
        }

        // Actions mit Filter holen
        $actions = Action::whereIn('action_type_sc', $actions_filter)
            ->join('action_states', 'action_states.sc', '=', 'actions.action_state_sc')
            ->join('action_types', 'action_types.sc', '=', 'actions.action_type_sc')
            ->select(['actions.*','action_types.name as at_name','action_states.name as as_name'])
            ->whereIn('action_state_sc', $states_filter)
            ->orderBy('action_date')
            ->get();

        // Teilnehmerzahlen ergänzen
        foreach ($actions as $action) {
            $action->am_kp = DB::table('action_members')
                ->join('members', 'members.webid', '=', 'action_members.member_id')
                ->where('action_id', $action->id)
                ->where('group','kp')
                ->select('members.nickname')
                ->value('nickname');
            $action->am_cr = DB::table('action_members')->where('action_id', $action->id)->where('group','cr')->count();
            $action->am_sv = DB::table('action_members')->where('action_id', $action->id)->where('group','sv')->count();
            $action->am_mf = DB::table('action_members')->where('action_id', $action->id)->where('group','mf')->count();
            $action->am_tn = DB::table('action_members')->where('action_id', $action->id)->where('group','tn')->count();
            $action->am_gs = DB::table('action_members')->where('action_id', $action->id)->sum('guests');
            Log::debug($action);
        }

        //Log::debug($actions);
        return view('actions.actions_list', compact('actions','sel_actions', 'sel_states'));
    }

    /**
     * Anzeigen des Erstellungsformulars für eine Aktivität
     */
    public function create(Request $request): View
    {
        Log::debug($request);
        //$action_type_sc = $request->input('action_type_sc') ?? 'gf';

        $def_vals = ['selected' => 'gf', 'action_type' => 'Gästefahrt', 'guests_max' => 20 ];

        return view('actions.action_create', compact('def_vals'));
    }

    /**
     * Speichern einer neuen Aktivität
     */
    public function store(Request $request):redirectResponse
    {
        //$validated = $request->validate([
        //    'action_type' => 'required|string|max:50',
        //]);

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
        return view('actions.action_details', [ 'action' => $action ]);
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
