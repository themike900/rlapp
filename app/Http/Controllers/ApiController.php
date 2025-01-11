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
    public function list(Request $request)
    {
        // POST-Daten in Members speichern, wenn webid noch nicht existiert
        $webid = $request->input('webid');

        if (!Members::where('webid', $webid)->exists()) {
            $member = new Members();
            $member->webid = $request->input('webid');
            $member->name = $request->input('name');
            $member->firstname = $request->input('firstname');
            $member->nickname = $request->input('nickname');
            $member->email = $request->input('email');
            $member->action_types = "vf,af,vt,mv,ar,abr";
            $member->save();
        }

        //$actions = DB::select('select * from list_actions where action_type_id in (?) order by action_date',['1, 2']);
        $actions = DB::table('list_actions')
            //->where('action_type_sc', 'vf')
            //->select('action_date', 'action_type', 'crew_start_at', 'crew_end_at')
            ->get();

        return response()->json($actions);
    }

    public function details(Request $request, int $web_id, int $action_id)
    {
        $action = Action::find($action_id);
        //$part =
        //$auth = $request.header('X-Auth-Token');
        /*$action = [
            'id' => '2',
            'action_date' => '2025-03-20',
            'action_type' => 'Gästefahrt',
            'crew_start_at' => '14:00',
            'crew_end_at' => '20:00',
            'action_start_at' => '15:00',
            'action_end_at' => '19:00',
            'action_type_sc' => 'gf',
            'action_state_sc' => 'of',
            'reason' => '70. Geburtstag',
            'applicant_name' => 'Max Mustermann',
            'applicant_email' => 'max@mustermann.de',
            'invoice_amount' => '1400',
            'guest_count' => '20',
            'catering_info' => 'Butter Lindner liefert',
            'ice_info' => 'Eis vom VSaW',
            'crew_supply' => 'Crew ist eingeladen',
            'additional_info' => ''
        ];*/
        $action['action_date'] = Carbon::createFromFormat('Y-m-d', $action['action_date'])->isoFormat('dddd DD.MM.');
        $action['crew_info'] = $action['crew_supply'];
        $action['service_info'] = "Catering: {$action['catering_info']},<br>Eis: {$action['ice_info']}";

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


    }
}
