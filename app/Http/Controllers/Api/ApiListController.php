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
     * Schritt 1: Neunanlegen eines Member, wenn er nicht schon existiert, aus den POST-Daten.
     * Schritt 2: Holen der Member-Daten aus der DB
     * Schritt 3: Aus der DB die Member-spezifischen Fahrtendaten holen und für die Webseite aufbereiten
     *
     * @param Request $request Member-Daten von der Webseite
     * @return JsonResponse Fahrtenlisten-Daten für die Webseite
     */
    public function __invoke(Request $request)
    {
        $request_input  = $request->all();
        Log::info("---- Getting {$request->input('list_type')} for {$request->input('webid')}------------------------------------------------");
        //Log::debug("Request: ".$request);

        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * POST-Daten in Member speichern, wenn webid noch nicht existiert
         *
         * in $anm_opt und $reg_guests_count bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
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
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'groups' => ''
            ]);
            Log::info('User aus webid neu angelegt');
        } else {
            DB::table('members')
                ->where('id', $member_id)
                ->update(['webid' => $web_id]);
            Log::info('User webid aktualisiert');
        }
        DB::table('members')->where('id', $member_id)->update(['last_access' => Carbon::now()]);

        // Log::debug($member_id);

        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Falls mehrfache member Datensätze entstehen, alle außer den ersten löschen,
         * weil gelegentlich bei der Erstanlage mehrere entstehen
         *
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
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

            Log::info('Doppelte User gelöscht.');
        }

        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Festlegen, welche action_types auf welcher Liste angezeigt werden
         *
         *
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        $list_type = match ($request->input('list_type')) {
            'Segeltermine' => ['sl','slbm'],
            'Veranstaltungen' => ['vl',],
            'Bereitschaft' => ['bm','slbm']
        };
        Log::debug('list_type: ' . print_r($list_type, true));

        /* -----------------------
            Gruppenzugehörigkeit des Member holen
        ------------------------- */
        $member_groups = DB::table('members')
            ->where('id', $member_id)
            ->value('groups');
        $member_groups_array = (!empty($member_groups)) ? explode(',', $member_groups) : [];
        if ($request->input('list_type') == 'Segeltermine' or $request->input('list_type') == 'Veranstaltungen') {
            $member_groups_array[] = 'tn';
        }
        Log::debug('member_groups_array: ' . print_r($member_groups_array, true));

        /* -----------------------
            Aktivitätentypen für diesen Member holen
        ------------------------- */
        $action_types = DB::table('action_types')
            ->whereIn('web_list', $list_type)
            ->get();
        Log::debug('action_types: ' . print_r($action_types, true));

        foreach ($action_types as $action_type) {
            if (!empty(array_intersect(explode(',', $action_type->groups), $member_groups_array))) {
                $list_action_types[] = $action_type->sc;
            }
        }
        Log::debug('list_action_types: ' . print_r($list_action_types, true));

        /* -----------------------
            für den Member sichtbare Aktivitäten holen
        ------------------------- */
        $actions = DB::table('list_actions')
            ->whereIn('action_type_sc', $list_action_types)
            ->whereIn('action_state_sc', ['of', 'gs'])
            ->orderBy('action_date')
            ->get();
        //Log::debug('actions: ' . print_r($actions, true));

        /* -----------------------
            in allen Fahrten Datum umformatieren und Anmeldestaus holen
        ------------------------- */
        foreach ($actions as $action) {
            $action->action_date = Carbon::createFromFormat('Y-m-d', $action->action_date)->isoFormat('dd DD.MM.');
            $action->start_at_text = (empty($action->crew_start_at)) ? 'Beginn' : 'an Bord';
            $action->end_at_text = (empty($action->crew_end_at)) ? 'Ende' : 'von Bord';
            $action->start_at = (empty($action->crew_start_at)) ? $action->action_start_at : $action->crew_start_at;
            $action->end_at = (empty($action->crew_end_at)) ? $action->action_end_at : $action->crew_end_at;

            $reg = DB::table('action_members')
                //->join('reg_state', 'action_members.reg_state', '=', 'reg_state.sc')
                ->where("web_id", $web_id)
                ->where('action_id', $action->action_id)
                ->first();
            Log::debug('action_members: ' . print_r($reg, true));

            $action->reg_state_name = '&nbsp;';
            if (!empty($reg)) {
                $reg_state = DB::table('reg_state')
                    ->where('sc', $reg->reg_state)
                    ->where('grp', $reg->group)
                    ->first();
                if ($request->input('list_type') == 'Segeltermine' and $reg->group == 'tn') {
                    $action->reg_state_name = $reg_state->name;
                }
                if ($request->input('list_type') == 'Bereitschaft' and in_array($reg->group, ['cr','sv','cr,sv','sf'])) {
                    $action->reg_state_name = $reg_state->name;
                }
                if ($request->input('list_type') == 'Veranstaltungen' and in_array($reg->group, ['tn','sh','wa'])) {
                    $action->reg_state_name = $reg_state->name;
                }
                Log::debug('reg_state: ' . print_r($reg_state, true));
            }

        }
        Log::debug("--- actions:\n " . print_r($actions, true));

        return response()->json($actions);
    }
}
