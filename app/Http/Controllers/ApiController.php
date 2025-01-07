<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Action;
//use function Laravel\Prompts\table;

class ApiController extends Controller
{
    public function list(Request $request)
    {
        $input = $request->all();
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
                'ID' => '12',
            ],
            [
                'Datum' => 'Mi 12.03.',
                'Anlass' => 'Gästefahrt',
                'anBord' => '14:00',
                'vonBord' => '20:00',
                'Status1' => 'geschlossen',
                'Status2' => '-',
                'ID' => '17',
            ],
            [
                'Datum' => 'So 16.03.',
                'Anlass' => 'Übungsfahrt',
                'anBord' => '16:00',
                'vonBord' => '20:00',
                'Status1' => 'offen',
                'Status2' => 'ja',
                'ID' => '18',
            ]
        ];
        return response()->json($list);
    }

    public function details(Request $request, int $web_id, int $action_id)
    {
        $action = Action::find($action_id);
        //$part =
        $auth = $request.header('X-Auth-Token');
        return response()->json(['web_id' => $web_id, 'action' => $action, 'auth' => $auth]);


    }

    public function register(Request $request)
    {
        $input = $request->all();
        return response()->json(['input' => $input]);
    }
}
