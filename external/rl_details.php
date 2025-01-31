<?php

function rl_details() {

    $user_id = get_current_user_id();
    $meta_weblist = get_user_meta($user_id, 'weblist',true) ?? 'Segelterminliste';
    //print_r($meta_weblist);
    $list_path = match($meta_weblist) {
        'Segeltermine' => '/intern/segeltermine-neu',
        'Bereitschaft' => '/intern/bereitschaft',
        'Veranstaltungen' => '/intern/veranstaltungen',
        default => '/intern/segeltermine'
    };
    $list_name = match($meta_weblist) {
        'Segeltermine' => 'zurück zur Segelterminliste',
        'Bereitschaft' => 'zurück zur Liste der Bereitschaftsmeldungen',
        'Veranstaltungen' => 'zurück zue Liste der Veranstaltungen',
        default => 'zurück zur Segelterminliste'
    };

    // URL-Parameter einlesen
    $urlPath= $_SERVER['REQUEST_URI'];
    $urlParts = parse_url($urlPath);
    parse_str($urlParts['query'], $urlQuery);
    $actionId = $urlQuery['id'];

    // UserID holen
    $webId = um_profile_id();

    // API-Request vorbereiten und ausführen
    $api_url = "rlapp.schummel.de/api/details/{$webId}/{$actionId}";
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
    ));
    $response = curl_exec($ch);
    //print_r(json_decode($response));

    // API-Response auswerten und aufbereiten
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        $out = 'cURL-Fehler: ' . $error;
    } else {
        $data = json_decode($response, true);
        $ac = $data['action'];
        $reg = $data['registered'];
        $mem = $data['members'];
        $anm = $data['anmeldung'];
    }

    // Zusammenbau der Webseite durchführen
    ob_start();
    //print_r($ac);

    ?>
    <p style="text-align: right"><a href="<?php echo $list_path; ?>?id=<?php echo $actionId?>"><?php echo $list_name ?></a></p>

    <table style="width:100%; border-collapse: collapse">

        <tr><td style="background-color: #b1d4fd"><b>Fahrteninformationen</b></td></tr>

        <tr>
            <td style="background-color: #d6e8ff;">
                <div style="display: flex; flex-direction: column; align-items: center;">
                    <table style="width:90%; border-collapse: collapse; background-color: white;">
                        <tr>
                            <td style="text-align: right;width: 30%">&nbsp;</td>
                            <td><b><?php echo $ac['action_name']; ?></b></td>
                        </tr>

                        <tr>
                            <td style="text-align: right;width: 30%"><b>Termin</b></td>
                            <td><b><?php echo $ac['action_date']; ?></b></td>
                        </tr>

                        <?php if (!empty($ac['crew_start_at'])) { ?>
                        <tr>
                            <td style="text-align: right"><b>Crew an Bord</b></td>
                            <td><b><?php echo $ac['crew_start_at'] . ' - ' . $ac['crew_end_at']; ?></b></td>
                        </tr>
                        <?php } ?>

                        <?php if (!empty($ac['action_start_at'])) { ?>
                        <tr>
                            <td style="text-align: right">Ab-/Anlegen</td>
                            <td><?php echo $ac['action_start_at'] . ' - ' . $ac['action_end_at']; ?></td>
                        </tr>
                        <?php } ?>

                        <?php if ($ac['action_type_sc'] == 'gf' and !empty($ac['reason'])) { ?>
                            <tr>
                                <td style="text-align: right">Anlass</td>
                                <td><?php echo $ac['reason']; ?></td>
                            </tr>
                        <?php } ?>

                        <?php if (!empty($ac['crew_info'])) { ?>
                        <tr>
                            <td style="text-align: right">Crew-Info</td>
                            <td><?php echo $ac['crew_info']; ?></td>
                        </tr>
                        <?php } ?>

                        <?php if ($ac['action_type_sc'] == 'gf') { ?>
                            <tr>
                                <td style="text-align: right; vertical-align: top;">Service-Info</td>
                                <td><?php echo $ac['service_info']; ?></td>
                            </tr>
                        <?php } ?>

                        <?php if (!empty($ac['additional_info'])) { ?>
                            <tr>
                                <td style="text-align: right; vertical-align: top;">Sonstige Infos</td>
                                <td><?php echo $ac['additional_info']; ?></td>
                            </tr>
                        <?php } ?>

                    </table>
                </div>
            </td>
        </tr>

        <tr><td style="background-color: #b1d4fd"><b>An- oder Abmeldung</b></td></tr>

            <tr>
                <td style="background-color: #d6e8ff">
                    <div style="display: flex; flex-direction: column; align-items: center">
                        <table style="width:90%; border-collapse: collapse; background-color: white">

                            <tr>
                                <td style="text-align: right;"><b>Anmeldung als mitfahrendes Vereinsmitglied</b></td>
                                <td>
                                    <form action="https://rlapp.schummel.de/api/rlreg" method="POST">
                                        <input type="hidden" name="webid" value="<?php echo $webId ?>">
                                        <input type="hidden" name="actionid" value="<?php echo $actionId ?>">
                                        <input type="hidden" name="group" value="tn">
                                        <input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
                                        &nbsp;<button type="submit">Anmelden</button>
                                    </form>
                                </td>
                            </tr>

                            <tr>
                                <td style="text-align: right;"><b>Die Fahrt ist bereits ausgebucht.<br>Du kannst dich auf die Warteliste setzen.</b></td>
                                <td>
                                    <form action="https://rlapp.schummel.de/api/rlreg" method="POST">
                                        <input type="hidden" name="webid" value="<?php echo $webId ?>">
                                        <input type="hidden" name="actionid" value="<?php echo $actionId ?>">
                                        <input type="hidden" name="group" value="tnwl">
                                        <input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
                                        <button type="submit">auf Warteliste</button>
                                    </form>
                                </td>
                            </tr>

                            <tr>
                                <td>Du bist als Teilnehmer angemeldet, und kannst Deine Teilnahme wieder abmelden.<br>Eventuelle Gäste werden auch mit abgemeldet.</td>
                                <td>
                                    <form action="https://rlapp.schummel.de/api/rlreg" method="POST">
                                        <input type="hidden" name="webid" value="<?php echo $webId?>">
                                        <input type="hidden" name="actionid" value="<?php echo $actionId?>">
                                        <input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
                                        <input type="hidden" name="abmeldung" value="1">
                                        <button type="submit">Abmelden</button>
                                    </form>
                                </td>
                            </tr>

                            <tr>
                                <td>Du stehst auf der Warteliste, und kannst dich wieder herausnehmen.<br>Eventuelle Gäste werden auch mit abgemeldet.</td>
                                <td>
                                    <form action="https://rlapp.schummel.de/api/rlreg" method="POST">
                                        <input type="hidden" name="webid" value="<?php echo $webId?>">
                                        <input type="hidden" name="actionid" value="<?php echo $actionId?>">
                                        <input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
                                        <input type="hidden" name="abmeldung" value="1">
                                        <button type="submit">Abmelden</button>
                                    </form>
                                </td>
                            </tr>

                            <tr>
                                <td>Du bist als Teilnehmer angemeldet. Die Fahrtenplanung ist abgeschlossen,<br>Abmeldungen sind nur noch über den Schiffsführer möglich.</td>
                                <td><b>Status: Teilnehmer</b></td>
                            </tr>

                            <tr>
                                <td>Die Fahrtenplanung ist abgeschlossen,<br>Anmeldungen sind nicht mehr möglich.</td>
                                <td><b>Status: nicht dabei</b></td>
                            </tr>

                            <tr>
                                <td style="text-align: right;">Du kannst hier deine Crew-Bereitschaftsmeldung abgeben.<br>
                                Wähle aus, ob du deine Bereitschaft für die Decks-Crew oder die Service-Crew, oder beide melden möchtest,
                                und ob du bei Ablehnung als normaler Teilnehmer mitfahren möchtest, oder nicht.</td>
                                <td style="vertical-align: top;">
                                    <form action="https://rlapp.schummel.de/api/rlreg" method="POST">
                                        <div style="display: flex; align-items: flex-start;">
                                            <!-- Checkbox-Container -->
                                            <div style="display: flex; flex-direction: column; gap: 10px; margin-right: 20px;">
                                              <label><input type="checkbox" name="groups[]" value="cr"> Crew</label>
                                              <label><input type="checkbox" name="groups[]" value="sv"> Service</label>
                                              <label><input type="checkbox" name="groups[]" value="tn"> Teilnehmer</label>
                                            </div>
                                            <input type="hidden" name="webid" value="<?php echo $webId ?>">
                                            <input type="hidden" name="actionid" value="<?php echo $actionId ?>">
                                            <input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
                                            <button type="submit">Melden</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>

                            <tr>
                                <td>Deine angefragten oder angenommen Gäste sind:<br>
                                - Karl-Heinz. Familie, angefragt<br>
                                - Lieselotte, Familie, angenommen<br>
                                - Friedrich Wilhelm Ganzlanger Name, befreundet, angenommen
                               </td>
                                <td>&nbsp;</td>
                            </tr>

                            <tr>
                                <td style="text-align: right;">Du kannst hier deine Crew-Bereitschaftsmeldung abgeben.<br>
                                Wähle aus, ob du deine Bereitschaft für die Decks-Crew oder die Service-Crew, oder beide melden möchtest.
                                </td>
                                <td style="vertical-align: top;">
                                    <form action="https://rlapp.schummel.de/api/rlreg" method="POST">
                                        <div style="display: flex; align-items: flex-start;">
                                            <!-- Checkbox-Container -->
                                            <div style="display: flex; flex-direction: column; gap: 10px; margin-right: 20px;">
                                              <label><input type="checkbox" name="groups[]" value="cr"> Crew</label>
                                              <label><input type="checkbox" name="groups[]" value="sv"> Service</label>
                                            </div>
                                            <input type="hidden" name="webid" value="<?php echo $webId ?>">
                                            <input type="hidden" name="actionid" value="<?php echo $actionId ?>">
                                            <input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
                                            <button type="submit">Melden</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>

                            <tr>
                                <td>Hier kannst Du Deiner Anmeldung weiteren Gast hinzufügen<br>
                                Die Teilnahme ist zunächst nur angefragt. Die Annahme oder Ablehnung wird Dir per Email gesendet, und wird dann hier auch sichtbar.</td>
                                <td>
                                    <form action="https://rlapp.schummel.de/api/rlreg" method="POST">
                                        <label>Name : <input type="text" name="gst_name" size="10" max="20"></label>
                                        <label>Bezug: <input type="text" name="gst_bezug" size="10" max="20"></label>
                                        <input type="hidden" name="webid" value="<?php echo $webId?>">
                                        <input type="hidden" name="actionid" value="<?php echo $actionId?>">
                                        <input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
                                        <input type="hidden" name="abmeldung" value="1">
                                        <button type="submit">Abmelden</button>
                                    </form>
                                </td>
                            </tr>

                        </table>
                    </div>
                </td>
            </tr>


        <tr><td style="background-color: #b1d4fd"><b>Angemeldete Teilnehmer</b></td></tr>

        <tr>
            <td style="background-color: #d6e8ff">
                <div style="display: flex; flex-direction: column; align-items: center">
                    <table style="width:70%; border-collapse: collapse; background-color: white">
                        <?php if ($ac['action_type_sc'] == 'vf' or $ac['action_type_sc'] == 'gf' or $ac['action_type_sc'] == 'uf' or $ac['action_type_sc'] == 'af') { ?>
                            <tr>
                                <td style="width: 30%;text-align: right;"><b>Kapitän</b></td>
                                <td><?php echo $mem['captain'];?></td>
                            </tr>
                            <tr>
                                <td style="width: 30%;text-align: right; vertical-align: top;"><b>Decks-Crew</b></td>
                                <td><?php echo $mem['crew'];?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($ac['action_type_sc'] == 'vf' or $ac['action_type_sc'] == 'gf') { ?>
                            <tr>
                                <td style="width: 30%;text-align: right; vertical-align: top;"><b>Service-Crew</b></td>
                                <td><?php echo $mem['service'];?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($ac['action_type_sc'] == 'vf') { ?>
                            <tr>
                                <td style="width: 30%;text-align: right; vertical-align: top;"><b>Mietfahrer</b></td>
                                <td><?php echo $mem['passengers'];?></td>
                            </tr>
                            <tr>
                                <td style="width: 30%;text-align: right; vertical-align: top;"><b>Gäste</b></td>
                                <td><?php echo $mem['guests'];?> (von maximal <?php echo $mem['guest_max'];?>)</td>
                            </tr>
                        <?php } ?>
                        <?php if ($ac['action_type_sc'] == 'vt' or $ac['action_type_sc'] == 'sc') { ?>
                            <tr>
                                <td style="width: 30%;text-align: right; vertical-align: top;"><b>Teilnehmer</b></td>
                                <td><?php echo $mem['participants'];?></td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </td>
        </tr>
    </table>
    <?php
    return ob_get_clean();
}
add_shortcode('rl_details', 'rl_details');

?>
