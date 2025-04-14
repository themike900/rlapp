<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParticipantsCalcService
{
    /**
     * @param $action_id int    ID der Aktivität
     * @param $reg_id int|null  ID der Anmeldung
     * @return array            mit diversen Zahlen
     */
    public function counts(int $action_id, int $reg_id = null): array
    {
        $action = DB::table('actions')
            ->where('id', $action_id)
            ->first();

        $action_type_sc = $action->action_type_sc;
        $cnt = [];
        $cnt['ac_max_guests'] = $action->ac_max_guests;
        $cnt['ac_max_pers'] = $action->ac_max_pers;

        /*// Anzahl Gäste für die Anmeldung $reg_id
        $cnt['reg_guests_count'] = 0;
        if ($reg_id) {
            $cnt['reg_guests_count'] = DB::table('guests')
                ->where('gst_action_id', $action_id)
                ->where('reg_id', '=', $reg_id)
                ->count();
        }*/

        // maximale Zahlen der Gruppen aus dem Aktivitäten-Typ
        //$max = DB::table('action_types')
        //    ->where('sc', $action_type_sc)
        //    ->value('groups');
        //$cnt['at_max'] = json_decode($max, true);

        // Anzahl angemeldeter Teilnehmer der Aktivität $action_id
        $cnt['ac_tn_ang'] = DB::table('action_members')
            ->where('action_id', $action_id)
            ->where('group', 'tn')
            ->where('reg_state', 'ang')
            ->count();

        // Anzahl angenommener Gäste der Aktivität $action_id
        $cnt['ac_guests_angn'] = DB::table('guests')
            ->where('gst_action_id', $action_id)
            ->where('gst_state', '=', 'angenommen')
            ->count();

        // Anzahl angefragten Gäste der Aktivität $action_id
        $cnt['ac_guests_angf'] = DB::table('guests')
            ->where('gst_action_id', $action_id)
            ->where('gst_state', '=', 'angefragt')
            ->count();

        // Anzahl der nicht abgelehnten Decks-Crew-Teilnehmer dieser Fahrt
        $cnt['ac_reg_cr'] = DB::table('action_members')
            ->where('action_id', $action_id)
            ->whereLike('group', '%cr%')
            ->whereNot('reg_state', 'abgl')
            ->count();

        // Anzahl der nicht abgelehnten Service-Crew-Teilnehmer dieser Fahrt
        $cnt['ac_reg_sv'] = DB::table('action_members')
            ->where('action_id', $action_id)
            ->whereLike('group', '%sv%')
            ->whereNot('reg_state', 'abgl')
            ->count();

        // Anzahl der nicht abgelehnten Decks+Service-Crew-Teilnehmer dieser Fahrt
        $cnt['ac_reg_crsv'] = DB::table('action_members')
            ->where('action_id', $action_id)
            ->where('group', 'cr,sv')
            ->whereNot('reg_state', 'abgl')
            ->count();

        // Bestimmung der Crew-Anzahl ohne Doppelzählung, aber mindestens 6
        $cnt_crew = $cnt['ac_reg_cr'] + $cnt['ac_reg_sv'] - $cnt['ac_reg_crsv'];
        $cnt['ac_crew'] = ($cnt_crew < 6) ? 6 : $cnt_crew;

        // Bestimmung der noch freien Gäste-Plätze
        $cnt['ac_guests_free'] = $cnt['ac_max_guests']
            - $cnt['ac_guests_angn'];

        // Bestimmung der freien Teilnehmer-Plätze für Fahrten
        if ( in_array($action_type_sc, ['vf','af','uf','gfx','gfm','bf'])) {
            $cnt['ac_tn_free'] = $cnt['ac_max_pers']  // maximale Plätze für die Fahrt
                - 1                                // minus ein Kapitän
                - $cnt['ac_guests_angn']            // minus angenommene Gäste
                - $cnt['ac_crew']                  // minus Crew (min 6)
                - $cnt['ac_tn_ang'];               // minus angemeldete Teilnehmer
        // Bestimmung der freien Teilnehmer-Plätze für Veranstaltungen
        } else {
            $cnt['ac_tn_free'] = $cnt['ac_max_pers']
                - $cnt['ac_guests_angn']
                - $cnt['ac_tn_ang'];
        }

        //Log::debug(print_r($cnt, true));

        /*  ac_max_guests
         *  ac_max_pers
         *  reg_guests_count:
         *  at_max
         *  ac_tn_ang
         *  ac_guests_angn
         *  ac_reg_cr
         *  ac_reg_sv
         *  ac_reg_crsv
         *  ac_crew
         *  ac_guest_free
         *  ac_tn_free
         */
        return $cnt;
    }
}
