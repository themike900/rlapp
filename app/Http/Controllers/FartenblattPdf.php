<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Action;
use Illuminate\Support\Facades\DB;

Carbon::setLocale('de');

class FartenblattPdf extends Controller
{
    public function generatePdf($actionId)
    {
        $action = Action::find($actionId); // Daten aus der Datenbank holen

        $action->action_date = Carbon::createFromFormat('Y-m-d', $action->action_date)->isoFormat('dddd DD. MMMM');

        // Nickname vom Kapitän holen (alle Fahrten)
        $members = [];
        $captain = (array)DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_members.action_id', $actionId)
            ->where('action_members.group', 'sf')
            ->select(['nickname','name','firstname'])
            ->first();
        if (!empty($captain)) {
            $members['captain'] = $captain['nickname'];
        } else {
            $members['captain'] = '';
        }

        //Nicknames der Crew-Mitglieder holen (alle Fahrten)
        $crew = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_members.action_id', $actionId)
            ->whereLike('action_members.group', '%cr%')
            ->whereNot('action_members.reg_state', 'abgl')
            ->orderBy('members.firstname')
            ->select(['nickname','name','firstname'])
            ->get();
        $members['crew'] = "&nbsp;";
        if (!empty($crew)) {
            $members['crew'] = [];
            foreach ($crew as $cr) {
                $members['crew'][] = $cr->firstname . ' ' . $cr->name;
            }
            $members['crew'] = implode(", ", $members['crew']);
        }

        // Nicknames der Service-Mitglieder holen (Gästefahrt, Vereinsfahrt, Ausbildungsfahrt)
        $service = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_members.action_id', $actionId)
            ->whereLike('action_members.group', '%sv%')
            ->whereNot('action_members.reg_state', 'abgl')
            ->orderBy('members.firstname')
            ->select(['nickname','name','firstname'])
            ->get();
        $members['service'] = "&nbsp;";
        if (!empty($service)) {
            $members['service'] = [];
            foreach ($service as $sv) {
                $members['service'][] = $sv->firstname . ' ' . $sv->name;
            }
            $members['service'] = implode(", ", $members['service']);
        }

        // Nicknames der Service-Mitglieder holen (Gästefahrt, Vereinsfahrt, Ausbildungsfahrt)
         $teilnehmer = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_members.action_id', $actionId)
            ->where('action_members.group', 'tn')
            ->orderBy('members.firstname')
            ->select(['nickname','name','firstname'])
            ->get();
        $members['teilnehmer'] = "&nbsp;";
        if (!empty($teilnehmer)) {
            $members['teilnehmer'] = [];
            foreach ($teilnehmer as $tn) {
                $members['teilnehmer'][] = $tn->firstname . ' ' . $tn->name;
            }
            $members['teilnehmer'] = implode(", ", $members['teilnehmer']);
        }


        $pdf = Pdf::loadView('layouts.fahrtenblatt', compact('action','members')); // Blade-Template in PDF umwandeln
        return $pdf->stream('fahrtenblatt.pdf'); // Direkt im Browser anzeigen
    }
}
