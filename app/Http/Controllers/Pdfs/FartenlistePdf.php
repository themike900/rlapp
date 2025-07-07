<?php

namespace App\Http\Controllers\Pdfs;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FartenlistePdf extends Controller
{
    public function generatePdf($webId_enc)
    {

        $decoded = base64_decode($webId_enc);
        list($webId,$rand) = explode('/', $decoded);
        Log::debug("$webId,$rand");

        $member = DB::table('members')
            ->where('webid', $webId)
            ->first();

        if (str_contains($member->groups,'cr') or str_contains($member->groups,'sf')) {
            $fahrten = ['vf', 'af', 'uf', 'gfx', 'bf'];
        } elseif (str_contains($member->groups,'tr')) {
            $fahrten = ['vf', 'af', 'gfx', 'bf'];
        } elseif (str_contains($member->groups,'sv')) {
            $fahrten = ['vf', 'af', 'gfx'];
        } else {
            $fahrten = ['vf', 'af'];
        }

        $actions = DB::table("actions")
            //->leftJoin('action_members', 'actions.id', '=', 'action_members.action_id')
            //->where('action_members.web_id', $webId)
            ->whereIn('action_state_sc', ['of', 'gs'])
            ->whereIn('action_type_sc', $fahrten)
            ->whereDate('action_date', '>=', date('Y-m-d'))
            ->orderBy('action_date')
            ->orderBy('action_start_at')
            ->get();

        foreach ($actions as $action) {
            $action->week = date('W', strtotime($action->action_date));
            $action->action_date = Carbon::createFromFormat('Y-m-d', $action->action_date)->isoFormat('dd DD.MM.');
            $reg = DB::table("action_members")
                ->where('action_id', $action->id)
                ->where('web_id', $webId)
                ->first();
            if ($reg) {
                $reg_state = $reg->group . '_' . $reg->reg_state;
                $action->reg_state_text = match ($reg_state) {
                    'cr_gpl'  => 'Crew geplant',
                    'cr_br'   => '? - Crew gemeldet',
                    'cr_abgl' => 'x - Crew abgesagt',
                    'sv_gpl'  => 'Service geplant',
                    'sv_br'   => '? - Service gemeldet',
                    'sv_abgl' => 'x - Service abgesagt',
                    'crsv_br' => '? - Crew/Service gemeldet',
                    'sf_ang'  => 'Schiffsführer',
                    'tn_ang'  => 'Teilnehmer',
                    'tn_wl'   => '? - Teilnehmer Warteliste'
                };
                $action->reg_color = match ($reg_state) {
                    'cr_gpl','sv_gpl','sf_ang','tn_ang'  => 'green',
                    'cr_br','sv_br','crsv_br','tn_wl'  => 'white',
                    'cr_abgl','sv_abgl' => 'red'
                };

                if ($reg->group == 'tn') {
                    $action->start_time = $action->action_start_at;
                    $action->end_time = $action->action_end_at;
                } else {
                    $action->start_time = $action->crew_start_at;
                    $action->end_time = $action->crew_end_at;
                }


            } else {
                $action->reg_state_text = '';
                $action->reg_color = 'white';
                if (str_contains($member->groups,'cr') or str_contains($member->groups,'sf') or str_contains($member->groups,'sv') or str_contains($member->groups,'tr')) {
                    $action->start_time = $action->crew_start_at;
                    $action->end_time = $action->crew_end_at;
                } else {
                    $action->start_time = $action->action_start_at;
                    $action->end_time = $action->action_end_at;
                }

            }
            $action->sf_name = DB::table('action_members')
                ->where('action_id', $action->id)
                ->where('group', 'sf')
                ->join('members', 'action_members.web_id', '=', 'members.webid')
                ->value('members.firstname') ?? '';

        }

        $regs = DB::table('action_members')
            ->join('actions', 'actions.id', '=', 'action_members.action_id')
            ->where('action_members.web_id', $webId)
            ->whereIn('action_members.reg_state', ['of', 'gs'])
            ->whereDate('action_date', '>=', date('Y-m-d'))
            ->orderBy('actions.action_date')
            ->orderBy('action_start_at')
            ->get();

        foreach ($regs as $reg) {
            $reg->week = date('W', strtotime($reg->action_date));
            $reg->action_date = Carbon::createFromFormat('Y-m-d', $reg->action_date)->isoFormat('dd DD.MM.');
        }

        Log::debug($regs);


        $pdf = Pdf::loadView('layouts.fahrtenliste', compact('actions', 'member')); // Blade-Template in PDF umwandeln
        return $pdf->stream('fahrtenliste.pdf'); // Direkt im Browser anzeigen
    }
}
