<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParticipantsListService
{
    public function getParticipantsList(int $action_id): array
    {
        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Die Namen aller angemeldeten Teilnehmer holen
         *
         * in $members bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        // Nickname vom Kapitän holen (alle Fahrten)
        $members = [];
        $captain = (array)DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_members.action_id', $action_id)
            ->where('action_members.group', 'sf')
            ->select(['nickname','name','firstname'])
            ->first();
        $members['captain'] = (!empty($captain)) ? $captain['firstname'] : '&nbsp;' ;


        //Nicknames der Crew-Mitglieder holen (alle Fahrten)
        $crew = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_members.action_id', $action_id)
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
            //$members['crew'] = implode(", ", $members['crew']);
        }

        // Nicknames der Service-Mitglieder holen (Gästefahrt, Vereinsfahrt, Ausbildungsfahrt)
        $service = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_members.action_id', $action_id)
            ->whereLike('action_members.group', '%sv%')
            ->whereNot('action_members.reg_state', 'abgl')
            ->orderBy('members.firstname')
            ->select(['nickname','name','firstname','fullname'])
            ->get();
        $members['service'] = "&nbsp;";
        if (!empty($service)) {
            $members['service'] = [];
            foreach ($service as $sv) {
                $members['service'][] = $sv->fullname;
            }
            //$members['service'] = implode("<br/>", $members['service']);
        }

        // Nicknames der Teilnehmer holen (Vereinsfahrt, Vereinstreffen, Shanty-Chor, ...)
        $participants = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_members.action_id', $action_id)
            ->where('action_members.group', 'tn')
            ->where('action_members.reg_state', 'ang')
            ->orderBy('members.firstname')
            ->select(['nickname','name','firstname','fullname'])
            ->get();
        $members['participants'] = "&nbsp;";
        if (!empty($participants)) {
            $members['participants'] = [];
            foreach ($participants as $pp) {
                $members['participants'][] = $pp->fullname;
            }
            //$members['participants'] = implode("<br>", $members['participants']);
        }

        // Nicknames der Wartelisten-Teilnehmer holen (Vereinsfahrt, Vereinstreffen, ...)
        $participants_wl = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.web_id')
            ->where('action_members.action_id', $action_id)
            ->where('action_members.group', 'tn')
            ->where('action_members.reg_state', 'wl')
            ->orderBy('members.firstname')
            ->select(['nickname','name','firstname','fullname'])
            ->get();
        $members['participants_wl'] = "&nbsp;";
        if (!empty($participants_wl)) {
            $members['participants_wl'] = [];
            foreach ($participants_wl as $pp) {
                $members['participants_wl'][] = $pp->fullname;
            }
            //$members['participants_wl'] = implode("<br/>", $members['participants_wl']);
        }
        return $members;

    }

}
