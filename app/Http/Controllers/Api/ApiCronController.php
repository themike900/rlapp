<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
//use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApiCronController extends Controller
{

    public function __invoke()
    {
        $closeDate = Carbon::now()->addDays(5);

        $closedActions = DB::table('actions')
            ->where('action_date', '<=', $closeDate)
            ->where('action_state_sc', 'of')
            ->whereIn('action_type_sc', ['vf','af','gfx','gfm','uf'])
            ->update(['action_state_sc' => 'gs']);

        return response($closedActions);

    }
}
