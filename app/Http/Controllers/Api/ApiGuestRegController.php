<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class ApiGuestRegController extends Controller
{
    /**
     * Schritt 1: Speichern eventuell mitgelieferter Anmelde-Daten aus den POST-Daten
     * Schritt 2: Zusammenstellen der Daten für die Anmelde-Webseite
     *
     * @param Request $request Anmelde-Daten
     * @return RedirectResponse Daten für die Anmelde-Webseite
     */
    public function __invoke(Request $request)
    {
        // wenn POST-Data kommen und wenn kein Eintrag in action_members ist, dann eintragen
        Log::debug('-----ApiGuestRegController.start -------------------------------------------');
        Log::debug($request->input());

        // wenn keine POST Daten, dann nichts machen
        if (!empty($request->input())) {

            $action_id = $request->input('action_id');
            $hostname =  $request->input('host');
            $abmeldung = $request->input('abmeldung');
            $reg_id =    $request->input('reg_id');
            $gst_name =  $request->input('gst_name');
            $gst_bezug = $request->input('gst_bezug');
            $guest_id =  $request->input('guest_id');
            $gst_state = $request->input('gst_state');


            /*++++++++++++++++++++++++++++++++++++++++++++++++++
             * Status der Aktivität holen
             *
             * in $action_state_sc bereitlegen
             * +++++++++++++++++++++++++++++++++++++++++++++++++
             */
/*
            // ist of=offen oder gs=geschlossen
            $action_state_sc =DB::table('actions')
                ->where('id', $action_id)
                ->value('action_state_sc');

            // ist tnon=TN-Anmeldung möglich oder tnoff=TN Anmeldung voll
            $ac_reg_state_tn =DB::table('actions')
                ->where('id', $action_id)
                ->value('ac_reg_state_tn');
*/

            /*++++++++++++++++++++++++++++++++++++++++++++++++++
             * Teilnehmerzahlen nach Gruppen, vor Veränderungen
             *
             * in $cnt bereitlegen
             * +++++++++++++++++++++++++++++++++++++++++++++++++
             */

            $action = DB::table('actions')
                ->where('id', $action_id)
                ->first();

            $cnt = new stdClass();
            $cnt->max_pers = $action->ac_max_pers;
            $cnt->max_guests = $action->ac_max_guests;
            $cnt->captain = 1;
            $cnt->crew_final = 5;
            $cnt->service_final = 1;

            // maximale Zahlen der Gruppen aus dem Fahrtentyp
            $max = DB::table('action_types')
                ->where('sc', $action->action_type_sc)
                ->value('groups_max');
            $cnt->at_max = json_decode($max, true);

            // Anzahl der angemeldeten Teilnehmer
            $cnt->ac_tn_ang = DB::table('action_members')
                ->where('action_id', $action_id)
                ->where('group', 'tn')
                ->where('reg_state', 'ang')
                ->count();

            // Anzahl angenommener Gäste
            $cnt->ac_guests_angn = DB::table('guests')
                ->where('gst_action_id', $action_id)
                ->where('gst_state', '=', 'angenommen')
                ->count();

            // Anzahl CR Bereitschaftsmeldungen mit CRSV
            $cnt->reg_cr = DB::table('action_members')
                ->where('action_id', $action_id)
                ->whereLike('group', '%cr%')
                ->whereNot('reg_state', 'abgl')
                ->count();

            // Anzahl SV Bereitschaftsmeldungen mit CRSV
            $cnt->reg_sv = DB::table('action_members')
                ->where('action_id', $action_id)
                ->whereLike('group', '%sv%')
                ->whereNot('reg_state', 'abgl')
                ->count();

            // Anzahl nur CRSV Bereitschaftsmeldungen
            $cnt->reg_crsv = DB::table('action_members')
                ->where('action_id', $action_id)
                ->where('group', 'cr,sv')
                ->whereNot('reg_state', 'abgl')
                ->count();

            $cnt_crew = $cnt->reg_cr + $cnt->reg_sv  - $cnt->reg_crsv;
            $cnt_crew = ($cnt_crew < 6) ?? 6;

            // noch freie Plätze
            $cnt->tn_free = $cnt->max_pers  // maximale Plätze für die Fahrt
                - 1                         // minus ein Kapitän
                - $cnt->ac_guests_angn      // minus angenommene Gäste
                - $cnt_crew                 // minus Crew (min 6)
                - $cnt->ac_tn_ang;          // minus angemeldete Teilnehmer

            // noch freie Plätze für Gäste
            $cnt->guest_free = $cnt->max_guests  // festgelegte maximal Gästezahl
                - $cnt->ac_guests_angn;          // angenommen Gäste

            Log::debug(print_r($cnt, true));

            /*++++++++++++++++++++++++++++++++++++++++++++++++++
             * Gast eintragen oder löschen in DB
             *
             *
             * +++++++++++++++++++++++++++++++++++++++++++++++++
             */

            // wenn kein Abmelde-Kennzeichen und gstName nicht leer, dann Gast anfragen
            if (empty($abmeldung) and !empty($gst_name))
            {

                // wenn Fahrt noch offen
                if ($action->action_state_sc == 'of') {

                    // Anmeldung nur, wenn TN und Gäste noch frei Plätze haben
                    if ($cnt->tn_free > 0 and $cnt->guest_free > 0 ) {
                        DB::table('guests')->insert([
                            'reg_id' => $reg_id,
                            'gst_action_id' => $action_id,
                            'gst_state' => 'angefragt',
                            'name' => $gst_name,
                            'reference' => $gst_bezug,
                            'created_at' => Carbon::now()
                        ]);

                        // Falls letzter freier Platz belegt wird, geht der TN-Status auf belegt
                        if ($cnt->tn_free == 1 ) {
                            DB::table('action')
                                ->where('id', $action_id)
                                ->update(['ac_reg_state_tn' => 'tnoff']);
                        }

                    } else {
                        // Fehlermeldung: gäste doch schon voll
                        DB::table('action_members')
                            ->where('id', $reg_id)
                            ->update(['reg_error' => 'guests_full']);
                    }

                } else {
                    DB::table('action_members')
                        ->where('id', $reg_id)
                        ->update(['reg_error' => 'ac_geschl']);
                }

                // Wenn Abmeldekennzeichen gesetzt, dann Abmelden
            } elseif (!empty($abmeldung)) {

                // wenn Fahrt tatsächlich noch offen
                if ($action->action_state_sc == 'of') {

                    // Gast löschen
                    DB::table('guests')
                        ->where('id', $guest_id)
                        ->delete();

                    // wenn TN voll war TN State wieder auf anmelden offen setzen
                    //if ($cnt->tn_free == 0 and $gst_state == 'angenommen') {
                    if ($action->ac_reg_state_tn == 'tnoff' and $gst_state == 'angenommen') {
                        DB::table('actions')
                            ->where('id', $action_id)
                            ->update(['ac_reg_state_tn' => 'tnon']);
                    }

                    // Wenn Fahrt doch schon geschlossen war Fehlermeldung kein Löschen mehr möglich
                } else {
                    DB::table('action_members')
                        ->where('id', $reg_id)
                        ->update(['reg_error' => 'ac_geschl']);
                }
            } elseif (empty($gst_name)) {
                DB::table('action_members')
                    ->where('id', $reg_id)
                    ->update(['reg_error' => 'empty_name']);
            // wenn doch ein Fehler auftritt
            } else {
                DB::table('action_members')
                    ->where('id', $reg_id)
                    ->update(['reg_error' => 'gst_error']);

            }

        }

        return redirect()->away("https://".$hostname."/intern/details?id=".$request->input("action_id")."&anm=true");
    }
}
