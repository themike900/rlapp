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
        if (!empty($captain)) {
            $members['captain'] = $captain['nickname'];
        } else {
            $members['captain'] = '&nbsp;';
        }

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
                $members['crew'][] = '&nbsp;&#8226; '.$cr->firstname . ' ' . $cr->name;
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
            ->select(['nickname','name','firstname'])
            ->get();
        $members['service'] = "&nbsp;";
        if (!empty($service)) {
            $members['service'] = [];
            foreach ($service as $sv) {
                $members['service'][] = '&nbsp;&#8226; '.$sv->firstname . ' ' . $sv->name;
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
            ->select(['nickname','name','firstname'])
            ->get();
        $members['participants'] = "&nbsp;";
        if (!empty($participants)) {
            $members['participants'] = [];
            foreach ($participants as $pp) {
                $members['participants'][] = '&nbsp;&#8226; '.$pp->firstname . ' ' . $pp->name;
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
            ->select(['nickname','name','firstname'])
            ->get();
        $members['participants_wl'] = "&nbsp;";
        if (!empty($participants_wl)) {
            $members['participants_wl'] = [];
            foreach ($participants_wl as $pp) {
                $members['participants_wl'][] = '&nbsp;&#8226; '.$pp->firstname . ' ' . $pp->name;
            }
            //$members['participants_wl'] = implode("<br/>", $members['participants_wl']);
        }
        return $members;

    }

}
