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
                'nickname' => $request->input('nickname'),
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
            ->whereIn('action_type', $member_action_types)
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

        if (DB::table('action_members')
            ->where('member_id', $web_id)
            ->where('action_id', $action_id)
            ->doesntExist()) {

            DB::table('action_members')->insert([
                'member_id' => 201,
                'action_id' => 4,
                'group' => 'cr',
                'guests' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        $action = Action::find($action_id);
        $action['action_date'] = Carbon::createFromFormat('Y-m-d', $action['action_date'])->isoFormat('dddd DD.MM.');
        $action['crew_info'] = $action['crew_supply'];
        $action['service_info'] = "Catering: {$action['catering_info']},<br>Eis: {$action['ice_info']}";

        $registered = DB::table('action_members')
            ->where('member_id', $web_id)
            ->get();

        $anmeldung = [
            'type' => 'gf',
            'crew_free' => "6",
            'service_free' => '2'
        ];
        $members = [
            'captain' => "Gerd K",
            'crew' => ["Michael S", "Matthias J", "Ulli F"],
            'service' => ["Silvia B", "Waltraud"]
        ];
        $members['crew'] = implode("<br>", $members['crew']);
        $members['service'] = implode("<br>", $members['service']);


        return response()->json(['action' => $action, "anmeldung" => $anmeldung, "members" => $members]);
        //return $request;


    }
}
