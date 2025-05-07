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
        Log::debug('--- ApiRegController.Start ---------------------------------------');
        // Log::debug($request->input());

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

            // maximale Zahlen der Gruppen dieses Fahrtentyps
            $max = DB::table('action_types')
                ->where('sc', $action['action_type_sc'])
                ->value('groups');
            $cnt->at_max = json_decode($max, true);

            // Anzahl angemeldeter Teilnehmer der Aktivität
            $cnt->ac_tn_ang = DB::table('action_members')
                ->where('action_id', $action_id)
                ->where('group', 'tn')
                ->where('reg_state', 'ang')
                ->count();

            // Anzahl angenommener Gäste dieser Aktivität
            $cnt->ac_guests_angn = DB::table('guests')
                ->where('gst_action_id', $action_id)
                ->where('gst_state', '=', 'angenommen')
                ->count();

            // Anzahl nicht abgelehnter Crew dieser Fahrt
            $cnt->reg_cr = DB::table('action_members')
                ->where('action_id', $action_id)
                ->whereLike('group', '%cr%')
                ->whereNot('reg_state', 'abgl')
                ->count();

            // Anzahl nicht abgelehnter Service dieser Fahrt
            $cnt->reg_sv = DB::table('action_members')
                ->where('action_id', $action_id)
                ->whereLike('group', '%sv%')
                ->whereNot('reg_state', 'abgl')
                ->count();

            // Anzahl nicht abgelehnter Crew+Service dieser Fahrt
            $cnt->reg_crsv = DB::table('action_members')
                ->where('action_id', $action_id)
                ->where('group', 'cr,sv')
                ->whereNot('reg_state', 'abgl')
                ->count();

            // Summe crew final + service final, 6
            $cnt->crsv_final = $cnt->crew_final + $cnt->service_final;
            // Anzahl nicht abgelehnter Crew + Service ohne doppelte
            $cnt->cnt_crew = $cnt->reg_cr + $cnt->reg_sv - $cnt->reg_crsv;
            //Log::debug('cnt_crew: '.$cnt->cnt_crew);
            // Anzahl nicht abgelehnte Crew + Service, mindestens 6
            $cnt->cnt_crew = ($cnt->cnt_crew < $cnt->crsv_final) ? $cnt->crsv_final : $cnt->cnt_crew;
            // Log::debug('cnt_crew: '.$cnt->cnt_crew);

            // Anzahl noch frei Gästeplätze (max Gäste - angenommene Gäste
            $cnt->guest_free = $cnt->max_guests
                - $cnt->ac_guests_angn;

            // Anzahl noch freier Teilnehmerplätze
            if ( in_array($action['action_type_sc'], ['vf','af','uf','gfx','gfm','bf'])) {
                $cnt->tn_free = $cnt->max_pers  // maximale Plätze für die Fahrt
                    - 1                         // minus ein Kapitän
                    - $cnt->ac_guests_angn      // minus angenommene Gäste
                    - $cnt->cnt_crew            // minus Crew (min 6)
                    - $cnt->ac_tn_ang;          // minus angemeldete Teilnehmer
            } else {
                $cnt->tn_free = $cnt->max_pers
                    - $cnt->ac_guests_angn
                    - $cnt->ac_tn_ang;
            }

            //Log::debug('ApiRegController.cnt');
            Log::debug("\nApiRegController.cnt" . print_r($cnt,true));

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
                    //action immer noch offen

                    //TODO Prüfen ob nicht doch schon voll

                    $opt_array = [
                        'anm_tn' => ['tn', 'ang'],
                        'anm_wl' => ['tn', 'wl'],
                        'bereit_crsv' => ['cr,sv', 'br'],
                        'bereit_cr' => ['cr', 'br'],
                        'bereit_sv' => ['sv', 'br'],
                    ];
                    // anm_opt im Request übersetzen zu reg_opt gem opt_array
                    $reg_opts = $opt_array[$request->input('anm_opt')];

                    // Überschreiben bei anm_opt = bereit_crsv
                    if ($request->input('anm_opt') == 'bereit_crsv') {
                        $reg_opts = ($request->input('groups') == ['cr']) ? ['cr', 'br'] : $reg_opts;
                        $reg_opts = ($request->input('groups') == ['sv']) ? ['sv', 'br'] : $reg_opts;
                    }

                    Log::debug("ApiRegController.reg_opts: " . print_r($reg_opts, true));

                    DB::table('action_members')->insert([
                        'web_id' => $web_id,
                        'action_id' => $action_id,
                        'group' => $reg_opts[0],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'reg_state' => $reg_opts[1],
                    ]);


                    // Aktivität war nur noch ein Platz frei, Teilnehmeranmeldung schließen
                    if (in_array($action['action_type_sc'], ['vf','af','bf','vr','wa']) && $cnt->tn_free == 1 && $reg_opts[0] == 'tn') {
                        Log::debug('ApiRegController.set_tnoff');
                        DB::table('actions')
                            ->where('id', $action_id)
                            ->update(['ac_reg_state_tn' => 'tnoff', 'updated_at' => Carbon::now()]);
                    }
                    // ub,gf keine TN, vt,sh,mv,afr,abr keine Maximalzahl

                } else {
                    // action doch schon geschlossen
                    DB::table('members')
                        ->where('webid', $web_id)
                        ->update(['reg_error' => 'ac_geschl']);
                }


                // Wenn Abmeldekennzeichen gesetzt, dann Abmelden
            } elseif (!empty($request->input('abmeldung'))) {

                //TODO prüfen ob abmelden überhaupt noch erlaubt ist
                Log::debug('ApiRegController.abmelden');

                $reg_id = DB::table('action_members')
                    ->where('web_id', $request->input('webid'))
                    ->where('action_id', $request->input('actionid'))
                    ->value('id');

                DB::table('action_members')
                    ->where('id', $reg_id)
                    ->delete();
                Log::debug('ApiRegController.deleted tn');

                DB::table('guests')
                    ->where('reg_id', $reg_id)->delete();
                Log::debug('ApiRegController.deleted guests');

                // Aktivität war Teilnehmeranmeldung geschlossen, wieder öffnen, oder WL nachrücken
                Log::debug('action_type_sc: '.$action['action_type_sc']);
                Log::debug('action_state_sc: '.$action['action_state_sc']);
                if (in_array($action['action_type_sc'], ['vf','af','bf','vr','wa']) && $action['ac_reg_state_tn'] == 'tnoff' && $anm_opt == 'abm_tn') {
                    if ($action['ac_with_wl'] == 0) {
                        // keine Warteliste, Teilnehmeranmeldung wieder öffnen
                        DB::table('actions')
                            ->where('id', $action_id)
                            ->update(['ac_reg_state_tn' => 'tnon', 'updated_at' => Carbon::now()]);
                        Log::debug('ApiRegController.nowl set tnon');
                    } else {
                        $wl_first = DB::table('action_members')
                            ->where('reg_state', 'wl')
                            ->where('action_id', $action_id)
                            ->orderBy('created_at')
                            ->first('id');
                        Log::debug('ApiRegController.wl_first');
                        Log::debug("ApiRegController.wl_first\n".print_r($wl_first, true));

                        if (!empty($wl_first)) {
                            DB::table('action_members')
                                ->where('id', $wl_first->id)
                                ->update(['reg_state' => 'ang','updated_at' => Carbon::now()]);
                            Log::debug('ApiRegController.tn_wl to tn_ang');

                        }
                    }
                }
            }

            //TODO Gäste löschen hinzufügen
        }

        //return;
        return redirect()->away("https://".$hostname."/intern/details?id=".$request->input("actionid")."&anm=true");
    }
}
