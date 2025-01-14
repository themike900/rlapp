<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Action;
use App\Models\Members;
//use function Laravel\Prompts\table;

Carbon::setLocale('de');

class ApiController extends Controller
{
    /**
     * Schritt 1: Neuenanlegen eines Memebers, wenn er nicht schon extiert, aus den POST-Daten.
     * Schritt 2: Holen der Member-Daten aus der DB
     * Schritt 3: Aus der DB die Member-spezifischen Fahrtendaten holen und für die Webseite aufbereiten
     *
     * @param Request $request Member-Daten von der Webseite
     * @return \Illuminate\Http\JsonResponse Fahrtenlisten-Daten für die Webseite
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
                'nickname' => $request->input('firstname') . ' ' . substr($request->input('lastname'), 0, 1),
                'email' => $request->input('email'),
                'action_types' => "vf,af,vt,mv,ar,abr",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'groups' => ''

            ]);
        }

        // Memberdaten aus der DB holen für seine Fahrtentypen
        $member_action_types = DB::table('members')
            ->where('webid', $web_id)
            ->select('action_types')
            ->first();
        $member_action_types = explode(',', $member_action_types->action_types);

        // für ihn sichtbare Fahrten holen
        $actions = DB::table('list_actions')
            //->whereIn('action_type', $member_action_types)
            ->whereIn('action_state', ['of','gs'])
            ->orderBy('action_date', 'asc')
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
     * @return \Illuminate\Http\JsonResponse Daten für die Anmelde-Webseite
     */
    public function details(Request $request, int $web_id, int $action_id)
    {
        //$auth = $request.header('X-Auth-Token');

        // wenn POST-Data und wenn kein Eintrag in action_members, dann eintragen
        if (!empty($request->input())) {

            if (DB::table('action_members')
                ->where('member_id', $web_id)
                ->where('action_id', $action_id)
                ->doesntExist()) {

                DB::table('action_members')->insert([
                    'member_id' => $web_id,
                    'action_id' => $action_id,
                    'group' => $request->input('group'),
                    'guests' => $request->input('guests', 0),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
        }

        // diese action holen und formatieren
        $action = Action::find($action_id);
        $action['action_date'] = Carbon::createFromFormat('Y-m-d', $action['action_date'])->isoFormat('dddd DD.MM.');
        $action['crew_info'] = $action['crew_supply'];
        $action['service_info'] = "Catering: {$action['catering_info']},<br>Eis: {$action['ice_info']}";

        // Registrierungsdaten holen, wenn für diese Fahrt registriert
        $registered = DB::table('action_members')
            ->join('groups', 'action_members.group', '=', 'groups.sc')
            ->where('action_members.member_id', $web_id)
            ->where('action_members.action_id', $action_id)
            //->select('id','group','name','guests')
            ->first();

        // TODO Anmeldung nur möglich, wenn Maximalzahlen nicht überschritten
        $anmeldung = [
            'type' => 'gf',
            'crew_free' => "6",
            'service_free' => '2'
        ];

        // Nickname vom Kapitän holen
        $members = [];
        $captain = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
            ->where('action_members.action_id', $action_id)
            ->where('action_members.group', 'kp')
            ->select('nickname')
            ->first();
        if (!empty($captain)) { $members['captain'] = $captain->nickname; } else { $members['captain'] = '&nbsp;'; }

        //Nicknames der Crew-Mitglieder holen
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

        // Nicknames der Service-Mitglider holen
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

        return response()->json(['action' => $action, "anmeldung" => $anmeldung, "members" => $members , "registered" => $registered, "request" => $request->input() ]);
        //return $request;

    }
}
