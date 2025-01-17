<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Action;
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
        // POST-Daten in Members speichern, wenn webid noch nicht existiert
        $web_id = $request->input('webid');

        if (DB::table('members')->where('webid', $web_id)->doesntExist()) {

            DB::table('members')->insert([
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
        }

        // Memberdaten aus der DB holen für seine Fahrtentypen, derzeit nicht verwendet
        $member_action_types = DB::table('members')
            ->where('webid', $web_id)
            ->select('action_types')
            ->first();
        $member_action_types = explode(',', $member_action_types->action_types);

        // für ihn sichtbare Fahrten holen
        $actions = DB::table('list_actions')
            //->whereIn('action_type', $member_action_types)
            ->whereIn('action_state', ['of', 'gs'])
            ->orderBy('action_date')
            ->get();

        // in allen Fahrten Datum umformatieren und Anmeldestaus holen
        foreach ($actions as $action) {
            $action->action_date = Carbon::createFromFormat('Y-m-d', $action->action_date)->isoFormat('dd DD.MM.');
            if (DB::table('action_members')->where("member_id", $web_id)->where('action_id', $action->action_id)->exists()) {
                $action->reg = 'ja';
            } else {
                $action->reg = '&nbsp;';
            }
        }

        return response()->json($actions);
    }

    /**
     * Schritt 1: Speichern eventuell mitgelieferter Anmelde-Daten aus den POST-Daten
     * Schritt 2: Zusammenstellen der Daten für die Anmelde-Webseite
     *
     * @param Request $request Anmelde-Daten oder leer
     * @param int $web_id ID des Webseiten-Nutzers
     * @param int $action_id ID der Fahrt
     * @return JsonResponse Daten für die Anmelde-Webseite
     */
    public function details(Request $request, int $web_id, int $action_id)
    {
        //$auth = $request.header('X-Auth-Token');

        // wenn POST-Data kommen und wenn kein Eintrag in action_members ist, dann eintragen
        if (!empty($request->input())) {

            if (DB::table('action_members')
                    ->where('member_id', $web_id)
                    ->where('action_id', $action_id)
                    ->doesntExist()
                and
                empty($request->input('abmeldung'))
            ) {

                DB::table('action_members')->insert([
                    'member_id' => $web_id,
                    'action_id' => $action_id,
                    'group' => $request->input('group'),
                    'guests' => $request->input('guests', 0),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            } elseif ($request->input('abmeldung') == 1) {

                DB::table('action_members')
                    ->where('member_id', $web_id)
                    ->where('action_id', $action_id)
                    ->delete();
            }
        }

        // diese action holen und formatieren
        $action = Action::find($action_id);
        $action['action_date'] = Carbon::createFromFormat('Y-m-d', $action['action_date'])->isoFormat('dddd DD.MM.');
        $action['crew_info'] = $action['crew_supply'];
        $action['service_info'] = "Catering: {$action['catering_info']},<br>Eis: {$action['ice_info']}";
        $action['action_name'] = DB::table('action_types')
            ->where('sc', $action['action_type_sc'])
            ->value('name');

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
        //$anmeldung = [];
        //$registered = [];
        //$members = [];
        //$max_array = [];

        return response()->json(['action' => $action, "anmeldung" => $anmeldung, "members" => $members, "registered" => $registered, "request" => $request->input(), "max_array" => $max_array]);
        //return $request;

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
        return redirect()->away("https://rlweb.schummel.de/details?id=".$request->input("actionid"));
    }

}
