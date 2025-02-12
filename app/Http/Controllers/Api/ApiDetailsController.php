<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Action;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

//use function Laravel\Prompts\table;

//use function Laravel\Prompts\table;

Carbon::setLocale('de');

class ApiDetailsController extends Controller
{

    /**
     *
     * Schritt 1: Zusammenstellen der Daten für die Anmelde-Webseite
     *
     * @param Request $request leer
     * @param int $web_id ID des Webseiten-Nutzers
     * @param int $action_id ID der Fahrt
     * @return JsonResponse Daten für die Anmelde-Webseite
     */
    public function __invoke(Request $request, int $web_id, int $action_id)
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
        //Log::debug($action);

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
            $reg_guests = DB::table('guests')
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
        $ac_guests_angf = DB::table('guests')
            ->where('gst_action_id', $action_id)
            ->where('gst_state', 'angefragt')
            ->count();

        $ac_guests_count['angn'] = $ac_guests_angn;
        $ac_guests_count['angf'] = $ac_guests_angf;

        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Testdaten bereitstellen
         *
         * in $anm_test bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        $anm_test['web_id'] = $web_id;
        $anm_test['action_id'] = $action_id;
        $anm_test['web_list'] = $web_list;
        $anm_test['mem_groups'] = $mem_groups;
        $anm_test['action_type_sc'] = $action['action_type_sc'];
        $anm_test['action_state_sc'] = $action['action_state_sc'];
        $anm_test['ac_reg_state_tn'] = $action['ac_reg_state_tn'];
        $anm_test['ac_reg_state_cr'] = $action['ac_reg_state_cr'];
        $anm_test['ac_reg_state_sv'] = $action['ac_reg_state_sv'];
        $anm_test['ac_guests_angn'] = $ac_guests_angn;
        $anm_test['ac_guests_free'] = $ac_guests_free;
        $anm_test['reg_reg_state'] = $reg_reg_state;
        $anm_test['reg_guests_count'] = $reg_guests_count;
        $anm_test['reg_id'] = $registered->id ?? null;



        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Anmeldeoptionen für die Webseiten auswählen
         *
         * in $anm_opt bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        // Segelterminliste, Teilnehmer An-Abmeldung -------------------------------------------------------------------
        if ($web_list == 'Segeltermine' or $web_list == 'Veranstaltungen') {
            // Mitglied nicht angemeldet
            if (in_array($reg_reg_state, [null, 'cr_abgl', 'sv_abgl'], true)) {
                // Fahrtenplanung offen.
                if ($action['action_state_sc'] == 'of') {
                    // Teilnehmer-Anmeldung noch offen, TN < max
                    if ($action['ac_reg_state_tn'] == 'tnon') {
                        $anm_opt[] = match ($action['action_type_sc']) {
                            'vf' => 'anm_mv',
                            'af' => 'anm_le',
                            default => 'anm_tn',
                        };
                    }
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
        $ac_regs_array = [];
        foreach ($regs as $reg) {
            $ac_regs_array[] = str_replace(',', '', $reg->group) . '_' . $reg->reg_state;
        }
        $ac_regs_count = array_count_values($ac_regs_array);

        foreach ( ['cr_br', 'cr_gpl', 'sv_br', 'sv_gpl', 'crsv_br', 'tn_ang', 'tn_wl', 'sf_ang'] as $rs) {
            $ac_regs_count[$rs] = (empty($ac_regs_count[$rs])) ? 0 : $ac_regs_count[$rs];
        }

        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Die Nicknames aller angemeldeten Teilnehmer holen
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
        //$members['guest_max'] = $action['ac_max_guests'];


        return response()->json([
            'action' => $action,
            "anm_opt" => $anm_opt,
            "reg_guests" => $reg_guests ?? [],
            "reg_id" => $registered->id ?? null,
            "reg_error" => $registered->reg_error ?? '',
            "anm_test" => $anm_test,
            "members" => $members,
            "ac_regs_count" => $ac_regs_count,
            "ac_guests_count" => $ac_guests_count ?? [],
            "debug" => true
        ]);

    }


}
