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

        if (DB::table('members')->where('webid', $webid)->doesntExist()) {

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

        // Memberdaten aus der DB holen
        $member = DB::table('members')->where('webid', $webid)->first();

        //$actions = DB::select('select * from list_actions where action_type_id in (?) order by action_date',['1, 2']);
        $actions = DB::table('actions')
            //->where('action_type_sc', 'vf')
            ->select('action_date', 'action_type', 'crew_start_at', 'crew_end_at')
            ->get();

        return response()->json($actions);
    }

    public function details(Request $request, int $web_id, int $action_id)
    {
        //$auth = $request.header('X-Auth-Token');

        DB::table('action_members')->insert([
            ['member_id' => 201,],
            ['action_id' => 4,],
            ['group' => 'cr',],
            ['guests' => 0,]
        ]);

        $action = Action::find($action_id);
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


        //return response()->json(['action' => $action, "anmeldung" => $anmeldung, "members" => $members]);
        return $request;


    }
}
