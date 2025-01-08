<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Action;
//use function Laravel\Prompts\table;

Carbon::setLocale('de');

class ApiController extends Controller
{
    public function list(Request $request)
    {
        //$input = $request->all();
        //$actions = DB::select('select * from list_actions where action_type_id in (?) order by action_date',['1, 2']);
        $actions = DB::table('list_actions')->get();
        $list = [
            [
                'Datum' => 'Sa 08.03.',
                'Anlass' => 'Vereinsfahrt',
                'anBord' => '12:00',
                'vonBord' => '16:00',
                'Status1' => 'geschlossen',
                'Status2' => 'ja',
                'ID' => '1',
            ],
            [
                'Datum' => 'Mi 12.03.',
                'Anlass' => 'Gästefahrt',
                'anBord' => '14:00',
                'vonBord' => '20:00',
                'Status1' => 'geschlossen',
                'Status2' => '-',
                'ID' => '2',
            ],
            [
                'Datum' => 'So 16.03.',
                'Anlass' => 'Übungsfahrt',
                'anBord' => '16:00',
                'vonBord' => '20:00',
                'Status1' => 'offen',
                'Status2' => 'ja',
                'ID' => '3',
            ]
        ];
        return response()->json($list);
    }

    public function details(Request $request, int $web_id, int $action_id)
    {
        //$action = Action::find($action_id);
        //$part =
        //$auth = $request.header('X-Auth-Token');
        $action = [
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
        ];
        $action['action_date'] = Carbon::createFromFormat('Y-m-d', $action['action_date'])->isoFormat('dddd DD.MM.');
        $action['crew_info'] = $action['crew_supply'];
        $action['service_info'] = "{$action['catering_info']}<br>{$action['ice_info']}&nbsp;";

        $anmeldung = [
            'type' => 'gf',
            'crew_free' => "6",
            'service_free' => '2'
        ];
        $members = [
            'captain' => "Gerd K",
            'crew' => ["Michael S", "Matthias J", "Ulli F"],
            'service' => ["Silvia B"]
        ];
        $members['crew'] = implode("<br>",$members['crew']);
        $members['service'] = implode("<br>",$members['service']);


        return response()->json(['action' => $action, "anmeldung" => $anmeldung, "members" => $members]);


    }

    public function register(Request $request)
    {
        $input = $request->all();
        return response()->json(['input' => $input]);
    }
}
