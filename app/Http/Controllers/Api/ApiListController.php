<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

Carbon::setLocale('de');

class ApiListController extends Controller
{
    /**
     * Schritt 1: Neunanlegen eines Members, wenn er nicht schon existiert, aus den POST-Daten.
     * Schritt 2: Holen der Member-Daten aus der DB
     * Schritt 3: Aus der DB die Member-spezifischen Fahrtendaten holen und für die Webseite aufbereiten
     *
     * @param Request $request Member-Daten von der Webseite
     * @return JsonResponse Fahrtenlisten-Daten für die Webseite
     */
    public function __invoke(Request $request)
    {
        Log::debug("Request: ".$request);
        // POST-Daten in Members speichern, wenn webid noch nicht existiert
        $web_id = $request->input('webid');

        $member_id = DB::table('members')
            ->where('webid', $request->input('webid'))
            ->value('id');

        if (empty($member_id)) {
            $member_id = DB::table('members')
                ->where('email', $request->input('email'))
                ->value('id');
        }
        if (empty($member_id)) {
            $member_id = DB::table('members')
                ->where('name', $request->input('name'))
                ->where('firstname', $request->input('firstname'))
                ->value('id');
        }

        if ( empty($member_id)) {

            $member_id = DB::table('members')->insertGetId([
                'webid' => $request->input('webid'),
                'name' => $request->input('name'),
                'firstname' => $request->input('firstname'),
                'nickname' => $request->input('firstname') . ' ' . substr($request->input('name'), 0, 1),
                'email' => $request->input('email'),
                'action_types' => "vf,af,vt,mv,ar,abr",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'groups' => ''
            ]);
        } else {
            DB::table('members')
                ->where('id', $member_id)
                ->update(['webid' => $web_id]);
        }
        DB::table('members')->where('id', $member_id)->update(['last_access' => Carbon::now()]);

        Log::debug($member_id);

        // Falls mehrfache member Datensätze entstehen, alle außer den ersten löschen,
        //  weil gelegentlich bei der Erstanlage mehrere entstehen
        if (DB::table('members')
                ->where('webid', $web_id)
                ->count() > 1) {

            $first = DB::table('members')
                ->where('webid', $web_id)
                ->orderBy('id')
                ->min('id');

            DB::table('members')
                ->where('webid', $web_id)
                ->where('id', '>', $first)
                ->delete();
        }

        // Auswahl für Anzeigeliste festlegen
        $list_type = match ($request->input('list_type')) {
            'Segeltermine' => ['sl','slbm'],
            'Veranstaltungen' => ['vl',],
            'Bereitschaft' => ['bm','slbm']
        };
        //Log::debug($list_type);
        $list_action_types = DB::table('action_types')
            ->whereIn('web_list', $list_type)
            ->pluck('sc')
            ->toArray();
        //Log::debug($list_action_types);

        // Gruppenzugehörigkeit des Members holen
        $member_groups = DB::table('members')
            ->where('id', $member_id)
            ->value('groups');
        $member_groups_array = explode(',', $member_groups);
        Log::debug(json_encode($member_groups_array));

        // für ihn sichtbare Fahrten holen
        $actions = DB::table('list_actions')
            ->whereIn('action_type_sc', $list_action_types)
            ->whereIn('action_state_sc', ['of', 'gs'])
            ->orderBy('action_date')
            ->get();
        //Log::debug("actions: ".json_encode($actions));

        // in allen Fahrten Datum umformatieren und Anmeldestaus holen
        foreach ($actions as $action) {
            $action->action_date = Carbon::createFromFormat('Y-m-d', $action->action_date)->isoFormat('dd DD.MM.');
            $action->start_at_text = (empty($action->crew_start_at)) ? 'Beginn' : 'an Bord';
            $action->end_at_text = (empty($action->crew_end_at)) ? 'Ende' : 'von Bord';
            $action->start_at = (empty($action->crew_start_at)) ? $action->action_start_at : $action->crew_start_at;
            $action->end_at = (empty($action->crew_end_at)) ? $action->action_end_at : $action->crew_end_at;

            $reg = DB::table('action_members')
                ->join('reg_state', 'action_members.reg_state', '=', 'reg_state.sc')
                ->where("member_id", $web_id)
                ->where('action_id', $action->action_id)
                ->first();

            if (!empty($reg)) {
                $action->reg_state_name = DB::table('reg_state')
                    ->where('sc', $reg->reg_state)
                    ->where('grp',$reg->group)
                    ->value('name');
            } else {
                $action->reg_state_name = '&nbsp;';
            }


        }

        return response()->json($actions);
    }
}
