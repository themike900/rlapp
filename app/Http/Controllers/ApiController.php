<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Action;
use Illuminate\Support\Facades\Log;
//use function Laravel\Prompts\table;

//use function Laravel\Prompts\table;

Carbon::setLocale('de');

class ApiController extends Controller
{
    /**
     * Schritt 1: Neunanlegen eines Members, wenn er nicht schon existiert, aus den POST-Daten.
     * Schritt 2: Holen der Member-Daten aus der DB
     * Schritt 3: Aus der DB die Member-spezifischen Fahrtendaten holen und für die Webseite aufbereiten
     *
     * @param Request $request Member-Daten von der Webseite
     * @return JsonResponse Fahrtenlisten-Daten für die Webseite
     */
    public function list(Request $request)
    {
        //Log::debug("Request: ".$request);
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

        // Memberdaten aus der DB holen für seine Fahrtentypen, derzeit nicht verwendet
        $member_action_types = DB::table('members')
            ->where('webid', $web_id)
            ->select('action_types')
            ->first();
        $member_action_types = explode(',', $member_action_types->action_types);
        //Log::debug($member_action_types);

        // für ihn sichtbare Fahrten holen
        $actions = DB::table('list_actions')
            ->whereIn('action_type_sc', $list_action_types)
            ->whereIn('action_state_sc', ['of', 'gs'])
            ->orderBy('action_date')
            ->get();
        Log::debug("actions: ".json_encode($actions));

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

                $action->reg_state_name = $reg->name;
            } else {
                $action->reg_state_name = '&nbsp;';
            }
        }

        return response()->json($actions);
    }

    /**
     *
     * Schritt 1: Zusammenstellen der Daten für die Anmelde-Webseite
     *
     * @param Request $request leer
     * @param int $web_id ID des Webseiten-Nutzers
     * @param int $action_id ID der Fahrt
     * @return JsonResponse Daten für die Anmelde-Webseite
     */
    public function details(Request $request, int $web_id, int $action_id)
    {
        //$auth = $request.header('X-Auth-Token');

        // diese action holen und formatieren
        $action = Action::find($action_id);
        $action['action_date'] = Carbon::createFromFormat('Y-m-d', $action['action_date'])->isoFormat('dddd DD.MM.');
        $action['crew_info'] = $action['crew_supply'];
        $action['service_info'] = "Catering: {$action['catering_info']},<br>Eis: {$action['ice_info']}";
        $action['action_name'] = DB::table('action_types')
            ->where('sc', $action['action_type_sc'])
            ->value('name');
        //Log::debug($action);

        //Registrierungsdaten holen, wenn für diese Fahrt registriert, ansonsten NULL
        $registered = DB::table('action_members')
            ->join('groups', 'action_members.group', '=', 'groups.sc')
            ->where('action_members.member_id', $web_id)
            ->where('action_members.action_id', $action_id)
            //->select('id','group','name','guests')
            ->first();

        // Anmeldung nur möglich, wenn Maximalzahlen nicht überschritten
        $crew_count = DB::table('action_members')
            ->where('action_id', $action_id)
            ->where('group', 'cr')
            ->count();
        $serv_count = DB::table('action_members')
            ->where('action_id', $action_id)
            ->where('group', 'sv')
            ->count();
        $pass_count = DB::table('action_members')
            ->where('action_id', $action_id)
            ->where('group', 'mf')
            ->count();
        $guest_count = DB::table('action_members')
            ->where('action_id', $action_id)
            ->sum('guests');

        $max = DB::table('action_types')
            ->where('sc', $action['action_type_sc'])
            ->value('groups');
        $max_array = json_decode($max, true);

        $free = [
            'crew_free' => (!empty($max_array['cr'])) ? $max_array['cr'] - $crew_count : '',
            'service_free' => (!empty($max_array['sv'])) ? $max_array['sv'] - $serv_count : '',
            'pass_free' => (!empty($max_array['mf'])) ? $max_array['mf'] - $pass_count - $guest_count : '',
            'guests_free' => $action['guest_count'] - $guest_count,
        ];
        $anmeldung = [];
        if ($action['action_type_sc'] == 'gf') {
            if ([$free['crew_free'] > 0]) $anmeldung[] = ['group' => 'cr', 'text' => 'Anmeldung für Decks-Crew', 'guests' => 0];
            if ([$free['service_free'] > 0]) $anmeldung[] = ['group' => 'sv', 'text' => 'Anmeldung für Service-Crew', 'guests' => 0];
        }
        if ($action['action_type_sc'] == 'vf') {
            if ([$free['crew_free'] > 0]) $anmeldung[] = ['group' => 'cr', 'text' => 'Anmeldung für Decks-Crew', 'guests' => $free['guests_free']];
            if ([$free['service_free'] > 0]) $anmeldung[] = ['group' => 'sv', 'text' => 'Anmeldung für Service-Crew', 'guests' => $free['guests_free']];
            if ([$free['pass_free'] > 0]) $anmeldung[] = ['group' => 'mf', 'text' => 'Anmeldung als Mitfahrer', 'guests' => $free['guests_free']];
        }
        if ($action['action_type_sc'] ==  'af' or $action['action_type_sc'] ==  'uf') {
            if ([$free['crew_free'] > 0]) $anmeldung[] = ['group' => 'cr', 'text' => 'Anmeldung für Decks-Crew', 'guests' => 0];
        }
        if ($action['action_type_sc'] ==  'vt' or $action['action_type_sc'] ==  'sc') {
            if ([$free['crew_free'] > 0]) $anmeldung[] = ['group' => 'tn', 'text' => 'Anmeldung als Teilnehmer', 'guests' => 0];
        }


        // Nickname vom Kapitän holen (alle Fahrten)
        $members = [];
        $captain = (array)DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
            ->where('action_members.action_id', $action_id)
            ->where('action_members.group', 'kp')
            ->select('nickname')
            ->first();
        if (!empty($captain)) {
            $members['captain'] = $captain['nickname'];
        } else {
            $members['captain'] = '&nbsp;';
        }

        //Nicknames der Crew-Mitglieder holen (alle Fahrten)
        $crew = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
            ->where('action_members.action_id', $action_id)
            ->where('action_members.group', 'cr')
            ->select('nickname')
            ->get();
        $members['crew'] = "&nbsp;";
        if (!empty($crew)) {
            $members['crew'] = [];
            foreach ($crew as $cr) {
                $members['crew'][] = $cr->nickname;
            }
            $members['crew'] = implode("<br>", $members['crew']);
        }

        // Nicknames der Service-Mitglieder holen (Gästefahrt, Vereinsfahrt)
        $service = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
            ->where('action_members.action_id', $action_id)
            ->where('action_members.group', 'sv')
            ->select('nickname')
            ->get();
        $members['service'] = "&nbsp;";
        if (!empty($service)) {
            $members['service'] = [];
            foreach ($service as $sv) {
                $members['service'][] = $sv->nickname;
            }
            $members['service'] = implode("<br>", $members['service']);
        }

        // Nicknames der Mitfahrer holen (Vereinsfahrt)
        $passengers = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
            ->where('action_members.action_id', $action_id)
            ->where('action_members.group', 'mf')
            ->select('nickname')
            ->get();
        $members['passengers'] = "&nbsp;";
        if (!empty($passengers)) {
            $members['passengers'] = [];
            foreach ($passengers as $ps) {
                $members['passengers'][] = $ps->nickname;
            }
            $members['passengers'] = implode("<br>", $members['passengers']);
        }

        // Nicknames der Teilnehmer holen (Vereinstreffen, Shanty-Chor, ...)
        $participants = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
            ->where('action_members.action_id', $action_id)
            ->where('action_members.group', 'tn')
            ->select('nickname')
            ->get();
        $members['participants'] = "&nbsp;";
        if (!empty($participants)) {
            $members['participants'] = [];
            foreach ($participants as $pp) {
                $members['participants'][] = $pp->nickname;
            }
            $members['participants'] = implode("<br>", $members['participants']);
        }

        $members['guests'] = $guest_count;
        $members['guest_max'] = $action['guest_count'];
        //$anmeldung = [];
        //$registered = [];
        //$members = [];
        //$max_array = [];

        return response()->json([
            'action' => $action,
            "anmeldung" => $anmeldung,
            "members" => $members,
            "registered" => $registered,
            "max_array" => $max_array,
            "webid" => $web_id,
            "actionid" => $action_id,
            "hello" => "hello"
        ]);

    }

    /**
     * Schritt 1: Speichern eventuell mitgelieferter Anmelde-Daten aus den POST-Daten
     * Schritt 2: Zusammenstellen der Daten für die Anmelde-Webseite
     *
     * @param Request $request Anmelde-Daten
     * @return RedirectResponse Daten für die Anmelde-Webseite
     */
    public function rlreg(Request $request)
    {
        // wenn POST-Data kommen und wenn kein Eintrag in action_members ist, dann eintragen
        Log::debug($request->input());

        if (!empty($request->input())) {

            if (DB::table('action_members')
                    ->where('member_id', $request->input('webid'))
                    ->where('action_id', $request->input('actionid'))
                    ->doesntExist()
                and
                empty($request->input('abmeldung'))
            ) {
                $group = $request->input('group');
                $reg_state = match($group) {
                    'cr', 'sv' => 'br',
                    default => 'ang'
                };
                DB::table('action_members')->insert([
                    'member_id' => $request->input('webid'),
                    'action_id' => $request->input('actionid'),
                    'group' => $group,
                    'guests' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'reg_state' => $reg_state
                ]);
            } elseif (!empty($request->input('abmeldung'))) {

                $del = DB::table('action_members')
                    ->where('member_id', $request->input('webid'))
                    ->where('action_id', $request->input('actionid'))
                    ->delete();
            }
        }

        //$referer = $request->headers->get('Referer');
        //$origin = $request->headers->get('Origin');
        //$hostname = ($referer) ? parse_url($referer, PHP_URL_HOST) : null;
        //$hostname = ($origin) ? parse_url($referer, PHP_URL_HOST) : $hostname;

        $hostname = $request->input('host');
        Log::debug("hostname: ".$hostname);

        return redirect()->away("https://{$hostname}/intern/details?id=".$request->input("actionid"));
        //return redirect()->away("https://www.royal-louise.de/intern/fahrtendetails?id=".$request->input("actionid"));

        //return response()->json(['request' => $request->input(), ]);
    }

}
