<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Action;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            ->select(['nickname','name','firstname','fullname'])
            ->first();
        if (!empty($captain)) {
            $members['captain'] = $captain['fullname'];
        } else {
            $members['captain'] = '';
        }

        //Nicknames der Crew-Mitglieder holen (alle Fahrten)
        $crew = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_members.action_id', $actionId)
            ->whereLike('action_members.group', '%cr%')
            ->where('action_members.reg_state', 'gpl')
            ->orderBy('members.firstname')
            ->select(['fullname','mobile'])
            ->get();
        $members['crew'] = "&nbsp;";
        if (!empty($crew)) {
            $members['crew'] = [];
            foreach ($crew as $cr) {
                $members['crew'][] = "$cr->fullname<br>&nbsp;&nbsp;&nbsp;<small>($cr->mobile)</small>";
            }
            $members['crew'] = implode(", ", $members['crew']);
        }
        //Log::debug($members['crew']);

        // Nicknames der Service-Mitglieder holen (Gästefahrt, Vereinsfahrt, Ausbildungsfahrt)
        $service = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_members.action_id', $actionId)
            ->whereLike('action_members.group', '%sv%')
            ->where('action_members.reg_state', 'gpl')
            ->orderBy('members.firstname')
            ->select(['fullname', 'mobile'])
            ->get();
        $members['service'] = "&nbsp;";
        if (!empty($service)) {
            $members['service'] = [];
            foreach ($service as $sv) {
                $members['service'][] = "$sv->fullname<br>&nbsp;&nbsp;&nbsp;<small>($sv->mobile)</small>";
            }
            $members['service'] = implode(", ", $members['service']);
        }
        //Log::debug($members['service']);

        // Nicknames der Service-Mitglieder holen (Gästefahrt, Vereinsfahrt, Ausbildungsfahrt)
        $teilnehmer = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_members.action_id', $actionId)
            ->where('action_members.group', 'tn')
            ->orderBy('members.firstname')
            ->select(['nickname','name','firstname','fullname'])
            ->get();
        $members['teilnehmer'] = "&nbsp;";
        if (!empty($teilnehmer)) {
            $members['teilnehmer'] = [];
            foreach ($teilnehmer as $tn) {
                $members['teilnehmer'][] = $tn->fullname;
            }
            $members['teilnehmer'] = implode(", ", $members['teilnehmer']);
        }

        // Namen der Gäste
        $guests = DB::table('guests')
            ->join('action_members', 'action_members.id', '=', 'guests.reg_id')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('guests.gst_state', '=', 'angenommen')
            ->where('guests.gst_action_id', $actionId)
            ->select(['members.fullname','guests.name'])
            ->get();
        Log::debug('guests: ' . print_r($guests, true));
        $members['guests'] = "&nbsp;";
        if (!empty($guests)) {
            $members['guests'] = [];
            foreach ($guests as $g) {
                $members['guests'][] = "$g->name<br>&nbsp;&nbsp;&nbsp;<small>($g->fullname)</small>";
            }
            $members['guests'] = implode(", ", $members['guests']);
        }

        Log::debug("members:" . print_r($members, true));

        $pdf = Pdf::loadView('layouts.fahrtenblatt', compact('action','members')); // Blade-Template in PDF umwandeln

        return $pdf->stream('fahrtenblatt.pdf'); // Direkt im Browser anzeigen
    }
}
