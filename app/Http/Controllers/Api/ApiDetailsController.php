<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Action;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ParticipantsCalcService;


Carbon::setLocale('de');

class ApiDetailsController extends Controller
{
    protected ParticipantsCalcService $participantsCalc;
    public function __construct(ParticipantsCalcService $participantsCalc) {
        $this->participantsCalc = $participantsCalc;
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

            // wenn reg_error nicht leer ist setze ihn wieder auf leer, für diesen Aufruf steht er noch in $registered->reg_error
            if (!empty($registered->reg_error)){
                DB::table('action_members')
                    ->where('id', $registered->id)
                    ->update(['reg_error' => '']);
            }
        }

        /*++++++++++++++++++++++++++++++++++++++++++++++++++
         * Wenn angemeldet: Die Daten meiner Gäste holen
         *
         * in $anm_opt und $reg_guests_count bereitlegen
         * +++++++++++++++++++++++++++++++++++++++++++++++++
         */
        $reg_guests_count = 0;
        if (!empty($registered)) {

            if ($action['action_state_sc'] == 'of'){

                // Name und Staus aller meiner Gäste
                $reg_guests = DB::table('guests')
                    ->where('reg_id', $registered->id)
                    ->orderBy('name')
                    ->get();

            } elseif ($action['action_state_sc'] == 'gs'){

                // Name und Staus meiner angenommenen Gäste
                $reg_guests = DB::table('guests')
                    ->where('reg_id', $registered->id)
                    ->where('gst_state', 'angenommen')
                    ->orderBy('name')
                    ->get();

            } else {
                $reg_guests = [];
            }

            // Anzahl meiner Gäste
            $reg_guests_count = DB::table('guests')
                ->where('gst_action_id', $action_id)
                ->where('reg_id', '=', $registered->id)
                ->count();
        }


        $ac_cnt = $this->participantsCalc->counts($action_id);

        $ac_guests_count = [
            'angn' => $ac_cnt['ac_guests_angn'],
            'angf' => $ac_cnt['ac_guests_angf']
        ];


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
        $anm_test['reg_id'] = $registered->id ?? null;
        $anm_test['reg_reg_state'] = $reg_reg_state;
        $anm_test['reg_guests_count'] = $reg_guests_count;
        $anm_test['reg_error'] = $registered->reg_error ?? '';

        $anm_test['ac_cnt'] = $ac_cnt;



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
                    if ($action['ac_reg_state_tn'] == 'tnoff') {
                        if ($action['action_state_sc'] == 'of') { $anm_opt[] = 'anm_wl'; }   // Teilnehmer-Anmeldung Warteliste.
                    }
                }
                // Fahrtenplanung abgeschlossen
                if ($action['action_state_sc'] == 'gs') { $anm_opt[] = 'anm_tn_geschl'; }    // Fahrtenplanung abgeschlossen.
            }
            if ($reg_reg_state == 'tn_ang') {
                if ($action['action_state_sc'] == 'of') {
                    if ($action['ac_max_guests'] > 0) {
                        $anm_opt[] = 'abm_tn';
                    } else {
                        $anm_opt[] = 'abm_tn_nogst';
                    }                                                   // Abmelden Teilnehmer
                    if ($ac_cnt['ac_guests_free'] > 0 and $action['ac_reg_state_tn'] == 'tnon') { $anm_opt[] = 'anfr_gst'; }  // Anfrage Gäste
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
            if (in_array($reg_reg_state, ['cr_gpl','sv_gpl','cr_br','sv_br','crsv_br'])) {
                $anm_opt[] = 'bereit_link';                                                   // Link zur Bereitschaftsliste
            }

        }

        // Crew-Bereitschaftsliste, und gehört das Mitglied zu CR oder SV
        if ($web_list == 'Bereitschaft' and !empty(array_intersect($mem_groups, ['cr','sv']))) {
            // noch keine Bereitschaft gemeldet
            if ( empty($reg_reg_state) ) {
                // gehört zu CR und SV
                if (in_array('cr', $mem_groups) and in_array('sv', $mem_groups)) {
                    // Fahrt CR bereit und SV bereit, dann Anmeldung CR und SV
                    if ($action['ac_reg_state_cr'] == 'crbr' and $action['ac_reg_state_sv'] == 'svbr') {
                        $anm_opt[] = 'bereit_crsv';                                                         // Bereitschaftsmeldung CR/SV
                    }

                    if ($action['ac_reg_state_cr'] == 'crbr' and $action['ac_reg_state_sv'] == 'svgpl') {
                        $anm_opt[] = 'bereit_cr';                                                           // Bereitschaftsmeldung CR
                    }
                    if ($action['ac_reg_state_cr'] == 'crgpl' and $action['ac_reg_state_sv'] == 'svbr') {
                        $anm_opt[] = 'bereit_sv';                                                           // Bereitschaftsmeldung SV
                    }
                    if ($action['ac_reg_state_cr'] == 'crgpl' and $action['ac_reg_state_sv'] == 'svgpl') {
                        $anm_opt[] = 'fertig_crsv';                                                         // Bereitschaft fertig geplant
                    }
                }
                // nur in Gruppe CR
                if (in_array('cr', $mem_groups) and !in_array('sv', $mem_groups)) {
                    if ($action['ac_reg_state_cr'] == 'crbr') {
                        $anm_opt[] = 'bereit_cr';                                                           // Bereitschaftsmeldung CR
                    }
                    if ($action['ac_reg_state_cr'] == 'crgpl') {
                        $anm_opt[] = 'fertig_crsv';                                                         // Bereitschaft fertig geplant
                    }
                }
                // nur in Gruppe SV
                if (in_array('sv', $mem_groups) and !in_array('cr', $mem_groups)) {
                    if ($action['ac_reg_state_sv'] == 'svbr') {
                        $anm_opt[] = 'bereit_sv';                                        // Bereitschaftsmeldung CR
                    }
                    if ($action['ac_reg_state_sv'] == 'svgpl') {
                        $anm_opt[] = 'fertig_crsv';                                      // Bereitschaft fertig geplant
                    }
                }
            }
            // CR bereitschaft gemeldet
            if ($reg_reg_state == 'cr_br') {
                // Bereitschaftsmeldung CR möglich
                if ($action['ac_reg_state_cr'] == 'crbr') {
                    if ($action['ac_max_guests'] > 0) {
                        $anm_opt[] = 'abm_cr';                                                 // CR angemeldet, Abmeldung online
                        if ($action['action_state_sc'] == 'of') {
                            if ($ac_cnt['ac_guests_free'] > 0) { $anm_opt[] = 'anfr_gst'; }               // Anfrage Gäste
                            if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list'; }             // Anzeige Gästeliste
                        }
                        if ($action['action_state_sc'] == 'gs') {
                            if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list_no_del'; }      // Anzeige Gästeliste, no delete
                        }
                    } else {
                        $anm_opt[] = 'abm_cr_nogst';                                            // CR angemeldet, Abmeldung online
                    }
                }
                if ($action['ac_reg_state_cr'] == 'crgpl') {
                    $anm_opt[] = 'abm_cr_tel';                                             // CR angemeldet, Abmeldung per Tel
                    if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list_no_del'; }         // Anzeige Gästeliste
                }
            }
            // CR Bereitschaft geplant
            if ($reg_reg_state == 'cr_gpl') {
                if ($action['ac_reg_state_cr'] == 'crgpl') {
                    $anm_opt[] = 'abm_cr_tel';
                    if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list_no_del'; }             // Anzeige Gästeliste
                }
            }
            // CR Bereitschaft abgelehnt
            if ($reg_reg_state == 'cr_abgl') {
                if ($action['ac_reg_state_cr'] == 'crgpl') {
                    $anm_opt[] = 'cr_abgl';
                }
            }
            // SV Bereitschaft gemeldet
            if ($reg_reg_state == 'sv_br'){
                if ($action['ac_reg_state_sv'] == 'svbr') {
                    if ($action['ac_max_guests'] > 0) {
                        $anm_opt[] = 'abm_sv';                                                 // SV angemeldet, Abmeldung online
                        if ($action['action_state_sc'] == 'of') {
                            if ($ac_cnt['ac_guests_free'] > 0) {
                                $anm_opt[] = 'anfr_gst';
                            }   // Anfrage Gäste
                            if ($reg_guests_count > 0) {
                                $anm_opt[] = 'gst_list';
                            }             // Anzeige Gästeliste
                        }
                        if ($action['action_state_sc'] == 'gs') {
                            if ($reg_guests_count > 0) {
                                $anm_opt[] = 'gst_list_no_del';
                            }      // Anzeige Gästeliste, no delete
                        }
                    } else {
                        $anm_opt[] = 'abm_sv_nogst';                                            // CR angemeldet, Abmeldung online
                    }
                }
                if ($action['ac_reg_state_sv'] == 'svgpl') {
                    $anm_opt[] = 'abm_sv_tel';                                             // SV angemeldet, Abmeldung per Tel
                }
            }
            // SV Bereitschaft geplant
            if ($reg_reg_state == 'sv_gpl') {
                if ($action['ac_reg_state_sv'] == 'svgpl') {
                    $anm_opt[] = 'abm_sv_tel';
                    if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list_no_del'; }      // Anzeige Gästeliste, no delete
                }
            }
            // SV Bereitschaft abgelehnt
            if ($reg_reg_state == 'sv_abgl') {
                if ($action['ac_reg_state_sv'] == 'svgpl') {
                    $anm_opt[] = 'sv_abgl';
                }
            }
            // als TN Link zur Segelterminliste
            if (in_array($reg_reg_state, ['tn_ang','tn_wl'])) {
                $anm_opt[] = 'segeltn_link';                                             // Link zur Segelterminliste
            }
            // Breitschaft CR und SV gemeldet
            if ($reg_reg_state == 'crsv_br') {
                if ($action['ac_max_guests'] > 0) {
                    $anm_opt[] = 'abm_crsv';                                             // CR/SV bereit gemeldet, Abmeldung beide
                    if ($action['action_state_sc'] == 'of') {
                        if ($ac_cnt['ac_guests_free'] > 0) {
                            $anm_opt[] = 'anfr_gst';
                        }   // Anfrage Gäste
                        if ($reg_guests_count > 0) {
                            $anm_opt[] = 'gst_list';
                        }             // Anzeige Gästeliste
                    }
                    if ($action['action_state_sc'] == 'gs') {
                        if ($reg_guests_count > 0) {
                            $anm_opt[] = 'gst_list_no_del';
                        }      // Anzeige Gästeliste, no delete
                    }
                } else {
                    $anm_opt[] = 'abm_crsv_nogst';                                            // CR angemeldet, Abmeldung online
                }
            }
            if ($action['ac_reg_state_cr'] == 'crgpl') {
                $anm_opt[] = 'abm_cr_tel';                                             // CR angemeldet, Abmeldung per Tel
                if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list_no_del'; }             // Anzeige Gästeliste
            }

        }
        // Eingeteilt als Schiffsführer
        if ($reg_reg_state == 'sf_ang') {
            $anm_opt[] = 'abm_sf_tel';
            if ($action['action_state_sc'] == 'of') {
                if ($ac_cnt['ac_guests_free'] > 0) { $anm_opt[] = 'anfr_gst'; }   // Anfrage Gäste
                if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list'; }             // Anzeige Gästeliste
            }
            if ($action['action_state_sc'] == 'gs') {
                if ($reg_guests_count > 0) { $anm_opt[] = 'gst_list_no_del'; }      // Anzeige Gästeliste, no delete
            }
        }

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
         * Die Namen aller angemeldeten Teilnehmer holen
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
            ->select(['nickname','name','firstname'])
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
            ->orderBy('members.firstname')
            ->select(['nickname','name','firstname'])
            ->get();
        $members['crew'] = "&nbsp;";
        if (!empty($crew)) {
            $members['crew'] = [];
            foreach ($crew as $cr) {
                $members['crew'][] = '&nbsp;&#8226; '.$cr->firstname . ' ' . $cr->name;
            }
            $members['crew'] = implode("<br>", $members['crew']);
        }

        // Nicknames der Service-Mitglieder holen (Gästefahrt, Vereinsfahrt, Ausbildungsfahrt)
        $service = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
            ->where('action_members.action_id', $action_id)
            ->whereLike('action_members.group', '%sv%')
            ->orderBy('members.firstname')
            ->select(['nickname','name','firstname'])
            ->get();
        $members['service'] = "&nbsp;";
        if (!empty($service)) {
            $members['service'] = [];
            foreach ($service as $sv) {
                $members['service'][] = '&nbsp;&#8226; '.$sv->firstname . ' ' . $sv->name;
            }
            $members['service'] = implode("<br>", $members['service']);
        }

        // Nicknames der Teilnehmer holen (Vereinsfahrt, Vereinstreffen, Shanty-Chor, ...)
        $participants = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
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
            $members['participants'] = implode("<br>", $members['participants']);
        }

        // Nicknames der Wartelisten-Teilnehmer holen (Vereinsfahrt, Vereinstreffen, ...)
        $participants_wl = DB::table('action_members')
            ->join('members', 'members.webid', '=', 'action_members.member_id')
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
            $members['participants_wl'] = implode("<br>", $members['participants_wl']);
        }

        $debug = is_string($member->control) && str_contains($member->control, 'debug');
        $mem_lists = is_string($member->control) && str_contains($member->control,'mlist');

        return response()->json([
            'action' => $action,
            "anm_opt" => $anm_opt,
            "reg_guests" => $reg_guests ?? [],
            "reg_id" => $registered->id ?? null,
            "reg_error" => $registered->reg_error ?? '',
            "member_name" => $member->firstname.' '.$member->name ?? '',
            "anm_test" => $anm_test,
            "members" => $members,
            "ac_regs_count" => $ac_regs_count,
            "ac_guests_count" => $ac_guests_count,
            "ac_tn_free" => $ac_cnt['ac_tn_free'],
            "debug" => $debug,
            "mem_lists" => $mem_lists,
        ]);

    }


}
