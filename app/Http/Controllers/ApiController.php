<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Action;
use Illuminate\Support\Facades\Log;
//use function Laravel\Prompts\table;

//use function Laravel\Prompts\table;

Carbon::setLocale('de');

class ApiController extends Controller
{
    /**
     * Schritt 1: Neunanlegen eines Members, wenn er nicht schon existiert, aus den POST-Daten.
     * Schritt 2: Holen der Member-Daten aus der DB
     * Schritt 3: Aus der DB die Member-spezifischen Fahrtendaten holen und für die Webseite aufbereiten
     *
     * @param Request $request Member-Daten von der Webseite
     * @return JsonResponse Fahrtenlisten-Daten für die Webseite
     */
    public function list(Request $request)
    {
        //Log::debug("Request: ".$request);
        // POST-Daten in Members speichern, wenn webid noch nicht existiert
        $web_id = $request->input('webid');

        $member_id = DB::table('members')
            ->where('webid', $request->input('webid'))
            ->value('id');
        if (empty($member_id)) {
            $member_id = DB::table('members')
                ->where('email', $request->input('email'))
                ->value('id');
        }
        if (empty($member_id)) {
            $member_id = DB::table('members')
                ->where('name', $request->input('name'))
                ->where('firstname', $request->input('firstname'))
                ->value('id');
        }

        if ( empty($member_id)) {

            $member_id = DB::table('members')->insertGetId([
                'webid' => $request->input('webid'),
                'name' => $request->input('name'),
                'firstname' => $request->input('firstname'),
                'nickname' => $request->input('firstname') . ' ' . substr($request->input('name'), 0, 1),
                'email' => $request->input('email'),
                'action_types' => "vf,af,vt,mv,ar,abr",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'groups' => ''
                ]);
        } else {
            DB::table('members')
                ->where('id', $member_id)
                ->update(['webid' => $web_id]);
        }
        DB::table('members')->where('id', $member_id)->update(['last_access' => Carbon::now()]);

        // Falls mehrfache member Datensätze entstehen, alle außer den ersten löschen,
        //  weil gelegentlich bei der Erstanlage mehrere entstehen
        if (DB::table('members')
                ->where('webid', $web_id)
                ->count() > 1) {

            $first = DB::table('members')
                ->where('webid', $web_id)
                ->orderBy('id')
                ->min('id');

            DB::table('members')
                ->where('webid', $web_id)
                ->where('id', '>', $first)
                ->delete();
        }

        // Auswahl für Anzeigeliste festlegen
        $list_type = match ($request->input('list_type')) {
            'Segeltermine' => ['sl','slbm'],
            'Veranstaltungen' => ['vl',],
            'Bereitschaft' => ['bm','slbm']
        };
        //Log::debug($list_type);
        $list_action_types = DB::table('action_types')
            ->whereIn('web_list', $list_type)
            ->pluck('sc')
            ->toArray();
        //Log::debug($list_action_types);

        // Memberdaten aus der DB holen für seine Fahrtentypen, derzeit nicht verwendet
        //$member_action_types = DB::table('members')
        //    ->where('webid', $web_id)
        //    ->select('action_types')
        //    ->first();
        //$member_action_types = explode(',', $member_action_types->action_types);
        //Log::debug($member_action_types);

        // für ihn sichtbare Fahrten holen
        $actions = DB::table('list_actions')
            ->whereIn('action_type_sc', $list_action_types)
            ->whereIn('action_state_sc', ['of', 'gs'])
            ->orderBy('action_date')
            ->get();
        Log::debug("actions: ".json_encode($actions));

        // in allen Fahrten Datum umformatieren und Anmeldestaus holen
        foreach ($actions as $action) {
            $action->action_date = Carbon::createFromFormat('Y-m-d', $action->action_date)->isoFormat('dd DD.MM.');
            $action->start_at_text = (empty($action->crew_start_at)) ? 'Beginn' : 'an Bord';
            $action->end_at_text = (empty($action->crew_end_at)) ? 'Ende' : 'von Bord';
            $action->start_at = (empty($action->crew_start_at)) ? $action->action_start_at : $action->crew_start_at;
            $action->end_at = (empty($action->crew_end_at)) ? $action->action_end_at : $action->crew_end_at;
            $reg = DB::table('action_members')
                ->join('reg_state', 'action_members.reg_state', '=', 'reg_state.sc')
                ->where("member_id", $web_id)
                ->where('action_id', $action->action_id)
                ->first();
            if (!empty($reg)) {

                $action->reg_state_name = $reg->name;
            } else {
                $action->reg_state_name = '&nbsp;';
            }
        }

        return response()->json($actions);
    }

    /**
     *
     * Schritt 1: Zusammenstellen der Daten für die Anmelde-Webseite
     *
     * @param Request $request leer
     * @param int $web_id ID des Webseiten-Nutzers
     * @param int $action_id ID der Fahrt
     * @return JsonResponse Daten für die Anmelde-Webseite
     */
    public function details(Request $request, int $web_id, int $action_id)
    {
        //$auth = $request.header('X-Auth-Token');
        $web_list = $request->input('liste');

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
         * Die Daten des aufrufenden Mitglieds holen
         *
         * in $ac_guests_free bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        $member = DB::table('members')
            ->where('webid', $web_id)
            ->first();
        $mem_groups = explode(',', $member->groups); // string to array



        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Teil1
         * Daten für die Anmeldeoptionen zusammenstellen
         *
         * in $anm_opt zurückgeben
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Die Daten einer eventuell vorhanden Anmeldung holen
         * und reg_state und group zusammensetzen
         *
         * in $registered bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        $registered = DB::table('action_members')
            ->where('member_id', $web_id)
            ->where('action_id', $action_id)
            ->first();

        $reg_reg_state = null;
        if (!empty($registered)){
            $reg_reg_state = str_replace(',', '', $registered->group) . '_' . $registered->reg_state;
        }

        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Wenn angemeldet: Die Daten meiner Gäste holen
         *
         * in $anm_opt und $reg_guests_count bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        $reg_guests_count = 0;
        if (!empty($registered)) {

            // Name und Staus meiner Gäste
            $anm_opt['reg_guests'] = DB::table('guests')
                ->where('reg_id', $registered->id)
                ->get();

            // Anzahl meiner Gäste
            $reg_guests_count = DB::table('guests')
                ->where('gst_action_id', $action_id)
                ->where('reg_id', '=', $registered->id)
                ->count();

        }

        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Die Anzahl der angemeldeten Gäste für diese Fahrt holen
         * wird auch für die Anmeldeoptionen benötigt
         *
         * in $ac_guests_free bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        // Anzahl angenommener Gäste für diese Fahrt
        $ac_guests_angn = DB::table('guests')
            ->where('gst_action_id', $action_id)
            ->where('gst_state', 'angenommen')
            ->count();
        $ac_guests_free = $action['ac_max_guests'] - $ac_guests_angn;


        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Testdaten bereitstellen
         *
         * in $anm_test bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        $anm_test['web_list'] = $web_list;
        $anm_test['mem_groups'] = $mem_groups;
        $anm_test['action_state_sc'] = $action['action_state_sc'];
        $anm_test['ac_reg_state_tn'] = $action['ac_reg_state_tn'];
        $anm_test['ac_reg_state_cr'] = $action['ac_reg_state_cr'];
        $anm_test['ac_reg_state_sv'] = $action['ac_reg_state_sv'];
        $anm_test['ac_guests_angn'] = $ac_guests_angn;
        $anm_test['ac_guests_free'] = $ac_guests_free;
        $anm_test['reg_reg_state'] = $reg_reg_state;
        $anm_test['reg_guests_count'] = $reg_guests_count;



        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Anmeldeoptionen für die Webseiten auswählen
         *
         * in $anm_opt bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        // Segelterminliste, Teilnehmer An-Abmeldung -------------------------------------------------------------------
        if ($web_list == 'Segeltermine' or $web_list == 'Veranstaltung') {
            // Mitglied nicht angemeldet
            if (in_array($reg_reg_state, [null, 'cr_abgl', 'sv_abgl'], true)) {
                // Fahrtenplanung offen.
                if ($action['action_state_sc'] == 'of') {
                    // Teilnehmer-Anmeldung noch offen, TN < max
                    if ($action['ac_reg_state_tn'] == 'tnon') { $anm_opt[] = 'anm_tn'; }     // Teilnehmer-Anmeldung.
                    // Teilnehmer-Anmeldung belegt
                    if ($action['ac_reg_state_tn'] == 'tnbl') {
                        if ($action['action_state_sc'] == 'of') { $anm_opt[] = 'anm_wl'; }   // Teilnehmer-Anmeldung Warteliste.
                    }
                }
                // Fahrtenplanung abgeschlossen
                if ($action['action_state_sc'] == 'gs') { $anm_opt[] = 'anm_tn_geschl'; }    // Fahrtenplanung abgeschlossen.
            }
            if ($reg_reg_state == 'tn_ang') {
                if ($action['action_state_sc'] == 'of') {
                    $anm_opt[] = 'abm_tn';                                                   // Abmelden Teilnehmer
                    if ($ac_guests_free > 0) { $anm_opt[] = 'anfr_gst'; }                    // Anfrage Gäste
                    if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list'; }                     // Anzeige Gästeliste
                }
                if ($action['action_state_sc'] == 'gs') {
                    $anm_opt[] = 'abm_tn_tel';                                               // Geschlossen, angemeldet
                    if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list_no_del'; }              // Anzeige Gästeliste, no delete
                }
            }
            if ($reg_reg_state == 'tn_wl') {
                if ($action['action_state_sc'] == 'of') {
                    $anm_opt[] = 'abm_tn_wl';                                                 // abmeldung Warteliste
                }
            }
            if (in_array($reg_reg_state, ['cr_gpl','sv_gpl','cr_br','sv_br'])) {
                $anm_opt[] = 'bereit_link';                                                   // Link zur Bereitschaftsliste
            }

        }

        // Crew-Bereitschaftsliste, und gehört das Mitglied zu CR oder SV
        if ($web_list == 'Bereitschaft' and !empty(array_intersect($mem_groups, ['cr','sv']))) {
            if ( empty($reg_reg_state) ) {
                if (in_array('cr', $mem_groups) and in_array('sv', $mem_groups)) {
                    if ($action['ac_reg_state_cr'] == 'crbr' and $action['ac_reg_state_sv'] == 'svbr') {
                        $anm_opt[] = 'bereit_crsv';                                                         // Bereitschaftsmeldung CR/SV
                    }
                    if ($action['ac_reg_state_cr'] == 'crbr' and $action['ac_reg_state_sv'] == 'svgp') {
                        $anm_opt[] = 'bereit_cr';                                                           // Bereitschaftsmeldung CR
                    }
                    if ($action['ac_reg_state_cr'] == 'crgp' and $action['ac_reg_state_sv'] == 'svbr') {
                        $anm_opt[] = 'bereit_sv';                                                           // Bereitschaftsmeldung SV
                    }
                    if ($action['ac_reg_state_cr'] == 'crgp' and $action['ac_reg_state_sv'] == 'svgp') {
                        $anm_opt[] = 'fertig_crsv';                                                         // Bereitschaft fertig geplant
                    }
                }
                if (in_array('cr', $mem_groups) and !in_array('sv', $mem_groups)) {
                    if ($action['ac_reg_state_cr'] == 'crbr') {
                        $anm_opt[] = 'bereit_cr';                                                           // Bereitschaftsmeldung CR
                    }
                    if ($action['ac_reg_state_cr'] == 'crgp') {
                        $anm_opt[] = 'fertig_crsv';                                                         // Bereitschaft fertig geplant
                    }
                }
                if (in_array('sv', $mem_groups) and !in_array('cr', $mem_groups)) {
                    if ($action['ac_reg_state_sv'] == 'svbr') {
                        $anm_opt[] = 'bereit_sv';                                                           // Bereitschaftsmeldung CR
                    }
                    if ($action['ac_reg_state_sv'] == 'svgp') {
                        $anm_opt[] = 'fertig_crsv';                                                         // Bereitschaft fertig geplant
                    }
                }
            }
            if ($reg_reg_state == 'cr_br') {
                if ($action['ac_reg_state_cr'] == 'crbr') {
                    $anm_opt[] = 'abm_cr';                                                 // CR angemeldet, Abmeldung online
                    if ($action['action_state_sc'] == 'of') {
                        if ($ac_guests_free > 0) { $anm_opt[] = 'anfr_gst'; }   // Anfrage Gäste
                        if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list'; }             // Anzeige Gästeliste
                    }
                    if ($action['action_state_sc'] == 'gs') {
                        if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list_no_del'; }      // Anzeige Gästeliste, no delete
                    }
                }
                if ($action['ac_reg_state_cr'] == 'crgp') {
                    $anm_opt[] = 'abm_cr_tel';                                             // CR angemeldet, Abmeldung per Tel
                }
            }
            if ($reg_reg_state == 'cr_abgl') {
                if ($action['ac_reg_state_cr'] == 'crgp') {
                    $anm_opt[] = 'cr_abgl';
                }
            }
            if ($reg_reg_state == 'sv_abg') {
                if ($action['ac_reg_state_cr'] == 'svgp') {
                    $anm_opt[] = 'sv_abgl';
                }
            }
            if ($reg_reg_state == 'sv_br'){
                if ($action['ac_reg_state_sv'] == 'svbr') {
                    $anm_opt[] = 'abm_sv';                                                 // SV angemeldet, Abmeldung online
                    if ($action['action_state_sc'] == 'of') {
                        if ($ac_guests_free > 0) { $anm_opt[] = 'anfr_gst'; }   // Anfrage Gäste
                        if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list'; }             // Anzeige Gästeliste
                    }
                    if ($action['action_state_sc'] == 'gs') {
                        if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list_no_del'; }      // Anzeige Gästeliste, no delete
                    }
                }
                if ($action['ac_reg_state_sv'] == 'svgp') {
                    $anm_opt[] = 'abm_sv_tel';                                             // SV angemeldet, Abmeldung per Tel
                }
            }
            if (in_array($reg_reg_state, ['tn_ang','tn_wl'])) {
                $anm_opt[] = 'segeltn_link';                                             // Link zur Segelterminliste
            }
            if ($reg_reg_state == 'crsv_br') {
                $anm_opt[] = 'abm_crsv';                                             // CR/SV bereit gemeldet, Abmeldung beide
                if ($action['action_state_sc'] == 'of') {
                    if ($ac_guests_free > 0) { $anm_opt[] = 'anfr_gst'; }   // Anfrage Gäste
                    if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list'; }             // Anzeige Gästeliste
                }
                if ($action['action_state_sc'] == 'gs') {
                    if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list_no_del'; }      // Anzeige Gästeliste, no delete
                }
            }
        }
        //if ($web_list == 'Veranstaltung') {}

        // falls noch kein Wert gesetzt ist:
        if ( empty($anm_opt) ) {
            $anm_opt[] = 'no_anm';
        }

        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Teil 2
         * Ab hier werden die Daten für den Teilnehmerbereich der Fahrt zusammenstellen
         *
         *
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
/*
        $max_pers = $action['ac_max_pers'];
        $captain = 1;
        $crew_final = 5;
        //$service_final = 1;
        //$max_guests = $action['ac_max_guests'];
        $free['tn_free'] = $max_pers - $captain - $crew_final;

       // maximale Zahlen der Gruppen aus dem Fahrtentyp
        $max = DB::table('action_types')
            ->where('sc', $action['action_type_sc'])
            ->value('groups');
        $max_array = json_decode($max, true);


        $free = [
            'crew_free' => (!empty($max_array['cr'])) ? $max_array['cr'] - $crew_count : '',
            'service_free' => (!empty($max_array['sv'])) ? $max_array['sv'] - $serv_count : '',
            'pass_free' => (!empty($max_array['mf'])) ? $max_array['mf'] - $pass_count - $guest_count : '',
            'guests_free' => $action['guest_count'] - $guest_count,
            'all_free' =>
        ];
*/

        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Anzahl der Teilnehmer in den Gruppen für diese Fahrt
         *
         * in array $regs_count bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        $regs = DB::table('action_members')
            ->where('action_id', $action_id)
            ->select(['group', 'reg_state'])
            ->get();
        $regs_array = [];
        foreach ($regs as $reg) {
            $regs_array[] = str_replace(',', '', $reg->group) . '_' . $reg->reg_state;
        }
        $regs_count = array_count_values($regs_array);

        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Die die Nicknames aller angemeldeten Teilnehmer holen
         *
         * in $members bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        // Nickname vom Kapitän holen (alle Fahrten)
        $members = [];
        $captain = (array)DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
            ->where('action_members.action_id', $action_id)
            ->where('action_members.group', 'sf')
            ->select('nickname')
            ->first();
        if (!empty($captain)) {
            $members['captain'] = $captain['nickname'];
        } else {
            $members['captain'] = '&nbsp;';
        }

        //Nicknames der Crew-Mitglieder holen (alle Fahrten)
        $crew = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
            ->where('action_members.action_id', $action_id)
            ->whereLike('action_members.group', '%cr%')
            ->select('nickname')
            ->get();
        $members['crew'] = "&nbsp;";
        if (!empty($crew)) {
            $members['crew'] = [];
            foreach ($crew as $cr) {
                $members['crew'][] = $cr->nickname;
            }
            $members['crew'] = implode("<br>", $members['crew']);
        }

        // Nicknames der Service-Mitglieder holen (Gästefahrt, Vereinsfahrt, Ausbildungsfahrt)
        $service = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
            ->where('action_members.action_id', $action_id)
            ->whereLike('action_members.group', '%sv%')
            ->select('nickname')
            ->get();
        $members['service'] = "&nbsp;";
        if (!empty($service)) {
            $members['service'] = [];
            foreach ($service as $sv) {
                $members['service'][] = $sv->nickname;
            }
            $members['service'] = implode("<br>", $members['service']);
        }

        // Nicknames der Teilnehmer holen (Vereinsfahrt, Vereinstreffen, Shanty-Chor, ...)
        $participants = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
            ->where('action_members.action_id', $action_id)
            ->where('action_members.group', 'tn')
            ->select('nickname')
            ->get();
        $members['participants'] = "&nbsp;";
        if (!empty($participants)) {
            $members['participants'] = [];
            foreach ($participants as $pp) {
                $members['participants'][] = $pp->nickname;
            }
            $members['participants'] = implode("<br>", $members['participants']);
        }

        //$members['guests'] = $ac_gst_count;
        $members['guest_max'] = $action['guest_count'];

        //$anmeldung = [];
        //$registered = [];
        //$members = [];
        //$max_array = [];

        return response()->json([
            'action' => $action,
            "anm_opt" => $anm_opt,
            "anm_test" => $anm_test,
            "members" => $members,
            "regs_count" => $regs_count,
            "debug" => true
        ]);

    }

    /**
     * Schritt 1: Speichern eventuell mitgelieferter Anmelde-Daten aus den POST-Daten
     * Schritt 2: Zusammenstellen der Daten für die Anmelde-Webseite
     *
     * @param Request $request Anmelde-Daten
     * @return RedirectResponse Daten für die Anmelde-Webseite
     */
    public function rlreg(Request $request)
    {
        // wenn POST-Data kommen und wenn kein Eintrag in action_members ist, dann eintragen
        Log::debug($request->input());

        if (!empty($request->input())) {

            if (DB::table('action_members')
                    ->where('member_id', $request->input('webid'))
                    ->where('action_id', $request->input('actionid'))
                    ->doesntExist()
                and
                empty($request->input('abmeldung'))
            ) {
                Log::debug($request->input('groups'));
                Log::debug(implode(',', $request->input('groups')));
                /*if (in_array('cr', $groups) || in_array('sv', $groups)) {
                    $reg_state = 'br';
                }*/
                if (!empty($request->input('groups'))) {
                    DB::table('action_members')->insert([
                        'member_id' => $request->input('webid'),
                        'action_id' => $request->input('actionid'),
                        'group' => implode(',', $request->input('groups')),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                        'reg_state' => $request->input('reg_state'),
                    ]);
                }
            } elseif (!empty($request->input('abmeldung'))) {

                $reg_id = DB::table('action_members')
                    ->where('member_id', $request->input('webid'))
                    ->where('action_id', $request->input('actionid'))
                    ->value('id');

                DB::table('action_members')
                    ->where('id', $reg_id)
                    ->delete();

                DB::table('guests')
                    ->where('reg_id', $reg_id)->delete();
            }
        }

        //$referer = $request->headers->get('Referer');
        //$origin = $request->headers->get('Origin');
        //$hostname = ($referer) ? parse_url($referer, PHP_URL_HOST) : null;
        //$hostname = ($origin) ? parse_url($referer, PHP_URL_HOST) : $hostname;

        $hostname = $request->input('host');
        //Log::debug("hostname: ".$hostname);

        return redirect()->away("https://".$hostname."/intern/details?id=".$request->input("actionid"));
        //return redirect()->away("https://www.royal-louise.de/intern/fahrtendetails?id=".$request->input("actionid"));

        //return response()->json(['request' => $request->input(), ]);
    }

}
