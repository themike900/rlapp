<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Action;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

class ApiRegController extends Controller
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
        Log::debug($request->input());

        // wenn keine POST Daten, dann nichts machen
        if (!empty($request->input())) {

            $action_id = $request->input('actionid');
            $web_id =    $request->input('webid');
            $hostname =  $request->input('host');
            $abmeldung = $request->input('abmeldung');
            $anm_opt =   $request->input('anm_opt');
            $tn_group =  $request->input('group');
            $br_groups = $request->input('groups');
            $guest_id =  $request->input('guest_id');


            /*++++++++++++++++++++++++++++++++++++++++++++++++++
             * Die Daten der Aktivität holen und formatieren
             *
             * in $action bereitlegen
             * +++++++++++++++++++++++++++++++++++++++++++++++++
             */
            $action = Action::find($action_id);
            $action['action_date'] = Carbon::createFromFormat('Y-m-d', $action['action_date'])->isoFormat('dddd DD.MM.');
            $action['crew_info'] = $action['crew_supply'];
            $action['service_info'] = "Catering: {$action['catering_info']},<br>Eis: {$action['ice_info']}";
            $action['action_type'] = DB::table('action_types')
                ->where('sc', $action['action_type_sc'])
                ->value('name');


            /*++++++++++++++++++++++++++++++++++++++++++++++++++
             * Teilnehmerzahlen nach Gruppen, vor Veränderungen
             *
             * in $cnt bereitlegen
             * +++++++++++++++++++++++++++++++++++++++++++++++++
             */
            $cnt = new stdClass();
            $cnt->max_pers = $action['ac_max_pers'];
            $cnt->max_guests = $action['ac_max_guests'];
            $cnt->captain = 1;
            $cnt->crew_final = 5;
            $cnt->service_final = 1;

            // maximale Zahlen der Gruppen aus dem Fahrtentyp
            $max = DB::table('action_types')
                ->where('sc', $action['action_type_sc'])
                ->value('groups');
            $cnt->at_max = json_decode($max, true);

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

            $cnt->reg_cr = DB::table('action_members')
                ->where('action_id', $action_id)
                ->whereLike('group', '%cr%')
                ->whereNot('reg_state', 'abgl')
                ->count();

            $cnt->reg_sv = DB::table('action_members')
                ->where('action_id', $action_id)
                ->whereLike('group', '%sv%')
                ->whereNot('reg_state', 'abgl')
                ->count();

            $cnt->reg_crsv = DB::table('action_members')
                ->where('action_id', $action_id)
                ->where('group', 'cr,sv')
                ->whereNot('reg_state', 'abgl')
                ->count();

            $cnt_crew = $cnt->reg_cr + $cnt->reg_sv - $cnt->reg_crsv;
            $cnt_crew = ($cnt_crew < 6) ?? 6;

            $cnt->guest_free = $cnt->max_guests
                - $cnt->ac_guests_angn;

            if ( in_array($action['action_type_sc'], ['vf','af','uf','gfx','gfm'])) {
                $cnt->tn_free = $cnt->max_pers  // maximale Plätze für die Fahrt
                    - 1                         // minus ein Kapitän
                    - $cnt->ac_guests_angn      // minus angenommene Gäste
                    - $cnt_crew                 // minus Crew (min 6)
                    - $cnt->ac_tn_ang;          // minus angemeldete Teilnehmer
            } else {
                $cnt->tn_free = $cnt->max_pers
                    - $cnt->ac_guests_angn
                    - $cnt->ac_tn_ang;
            }

            Log::debug(json_decode(json_encode($cnt), true));

            /*++++++++++++++++++++++++++++++++++++++++++++++++++
             * Anmeldung Mitglied eintragen oder löschen in DB
             *
             *
             * +++++++++++++++++++++++++++++++++++++++++++++++++
             */

            // wenn web_id nicht angemeldet und kein Abmelde-Kennzeichen, dann anmelden
            if (DB::table('action_members')
                    ->where('web_id', $web_id)
                    ->where('action_id', $action_id)
                    ->doesntExist()
                and
                empty($request->input('abmeldung'))
            ) {
                if ($action['action_state_sc'] == 'of') {

                    //TODO Prüfen ob nicht doch schon voll

                    $opt_array = [
                        'anm_tn' => ['tn', 'ang'],
                        'anm_wl' => ['wl', 'ang'],
                        'bereit_crsv' => ['cr,sv', 'br'],
                        'bereit_cr' => ['cr', 'br'],
                        'bereit_sv' => ['sv', 'br'],
                    ];
                    $reg_opts = $opt_array[$request->input('anm_opt')];

                    if ($request->input('anm_opt') == 'bereit_crsv') {
                        $reg_opts = ($request->input('groups') == ['cr']) ? ['cr', 'br'] : $reg_opts;
                        $reg_opts = ($request->input('groups') == ['sv']) ? ['sv', 'br'] : $reg_opts;
                    }

                    //Log::debug(print_r($reg_opts, true));

                    DB::table('action_members')->insert([
                        'web_id' => $web_id,
                        'action_id' => $action_id,
                        'group' => $reg_opts[0],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'reg_state' => $reg_opts[1],
                    ]);

                } else {
                    DB::table('members')
                        ->where('web_id', $web_id)
                        ->update(['reg_error' => 'ac_geschl']);
                }

                //TODO prüfen ob Status geändert werden muss

                // Wenn Abmeldekennzeichen gesetzt, dann Abmelden
            } elseif (!empty($request->input('abmeldung'))) {

                //TODO prüfen ob abmelden überhaupt noch erlaubt ist

                $reg_id = DB::table('action_members')
                    ->where('web_id', $request->input('webid'))
                    ->where('action_id', $request->input('actionid'))
                    ->value('id');

                DB::table('action_members')
                    ->where('id', $reg_id)
                    ->delete();

                DB::table('guests')
                    ->where('reg_id', $reg_id)->delete();

                //TODO prüfen, ob Status geändert werden muss
            }

            //TODO Gäste löschen hinzufügen
        }

        //return;
        return redirect()->away("https://".$hostname."/intern/details?id=".$request->input("actionid")."&anm=true");
    }
}
