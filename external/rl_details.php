<?php

function rl_details() {
    echo '<pre>';

    $user_id = get_current_user_id();
    $meta_weblist = get_user_meta($user_id, 'weblist',true) ?? 'Segeltermine';
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
    $api_url = "rlapp.schummel.de/api/details/{$webId}/{$actionId}?liste={$meta_weblist}";
	//print_r($api_url);
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
        //$reg = $data['registered'];
        //$mem = $data['members'];
        //$anm = $data['anmeldung'];
        $anm_opt = $data['anm_opt'] ?? ['no_anm'];
        $anm_test = $data['anm_test'];
    }
    // Zusammenbau der Webseite durchführen
    print_r($anm_opt);
    print_r($anm_test);

    echo '</pre>';
    ob_start();

    ?>
    <style>
        /* Container-Klasse für Isolation */
        .sc-container .custom-table-btn {
            background-color: #007BFF;
            border: 2px solid #0056b3;
            color: white;
            padding: 5px 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            display: inline-block;
            margin: 1px;
        }
        /* Hover-Effekt mit höherer Spezifität */
        .sc-container .custom-table-btn:hover {
            background-color: #4da1ff !important;
            border-color: #0056b3 !important;
            transform: scale(1.02) !important;
        }
        /* Tabellen-Reset */
        .sc-container table {
            border-collapse: collapse !important;
            margin: 20px 0 !important;
			font-size: 16px;
			line-height: 1.3;
        }
        .sc-container td {
            padding: 8px !important;
            text-align: left !important;
        }
        .checkbox {
            transform: scale(1.5);
            margin-right: 5px;
        }
    </style>

    <p style="text-align: right"><a href="<?php echo $list_path; ?>?id=<?php echo $actionId?>"><?php echo $list_name ?></a></p>

    <div class="sc-container"> <!-- Wrapper für Isolation -->
    <table>

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

                        <?php if (!empty(array_intersect(['anm_tn', 'all'], $anm_opt))) { ?>
                            <tr>
                                <td colspan="2">Du bist bisher nicht angemeldet</td>
                            </tr>
							<tr>
								<td>Anmeldung als <b>mitfahrendes Vereinsmitglied</b></td>
								<td>
									<form action="https://rlapp.schummel.de/api/rlreg" method="POST">
										<input type="hidden" name="webid" value="<?php echo $webId ?>">
										<input type="hidden" name="actionid" value="<?php echo $actionId ?>">
										<input type="hidden" name="group" value="tn">
										<input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
										<input type="hidden" name="anm_pot" value="<?php echo $anm_opt ?>">
										<button type="submit" class="custom-table-btn">Anmelden</button>
									</form>
								</td>
							</tr>
						<?php } ?>

					<!-- segeltl, mem_groups alle, no reg, action_state offen, ac_reg_state_tn belegt-->
                        <?php if (!empty(array_intersect(['anm_wl', 'all'], $anm_opt))) { ?>
                            <tr>
                                <td colspan="2">Du bist bisher nicht angemeldet</td>
                            </tr>
							<tr>
								<td>Die <b>Fahrt</b> ist bereits <b>ausgebucht</b>.Du kannst dich aber auf die <b>Warteliste</b> setzen.</td>
								<td>
									<form action="https://rlapp.schummel.de/api/rlreg" method="POST">
										<input type="hidden" name="webid" value="<?php echo $webId ?>">
										<input type="hidden" name="actionid" value="<?php echo $actionId ?>">
										<input type="hidden" name="group" value="tnwl">
										<input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
										<input type="hidden" name="anm_pot" value="<?php echo $anm_opt ?>">
										<button type="submit" class="custom-table-btn">auf Warteliste</button>
									</form>
								</td>
							</tr>
						<?php } ?>

					<!-- segeltl, alle, TN angem, offen, belegt egal-->
                        <?php if (!empty(array_intersect(['abm_tn', 'all'], $anm_opt))) { ?>
                            <tr>
                                <td colspan="2">Du bist als <b>Teilnehmer</b> angemeldet &#x2705;</td>
                            </tr>
							<tr>
								<td>Du kannst Deine Teilnahme wieder abmelden.<br>Eventuelle Gäste werden auch mit abgemeldet!!</td>
								<td>
									<form action="https://rlapp.schummel.de/api/rlreg" method="POST">
										<input type="hidden" name="webid" value="<?php echo $webId?>">
										<input type="hidden" name="actionid" value="<?php echo $actionId?>">
										<input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
										<input type="hidden" name="abmeldung" value="1">
										<input type="hidden" name="anm_pot" value="<?php echo $anm_opt ?>">
										<button type="submit" class="custom-table-btn">Abmelden</button>
									</form>
								</td>
							</tr>
						<?php } ?>

					<!-- segeltl, alle, TN WL, offen, belegt egal-->
                        <?php if (!empty(array_intersect(['abm_tn_wl', 'all'], $anm_opt))) { ?>
                            <tr>
                                <td colspan="2">Du stehst auf der <b>Warteliste</b> &#x2705;</td>
                            </tr>
							<tr>
								<td>Du kannst dich wieder herausnehmen.<br>Eventuelle Gäste werden auch mit abgemeldet!!</td>
								<td>
									<form action="https://rlapp.schummel.de/api/rlreg" method="POST">
										<input type="hidden" name="webid" value="<?php echo $webId?>">
										<input type="hidden" name="actionid" value="<?php echo $actionId?>">
										<input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
										<input type="hidden" name="abmeldung" value="1">
										<input type="hidden" name="anm_pot" value="<?php echo $anm_opt ?>">
										<button type="submit" class="custom-table-btn">Abmelden</button>
									</form>
								</td>
							</tr>
						<?php } ?>

					<!-- segeltl, alle, TN angem, geschlossen, belegt egal-->
                        <?php if (!empty(array_intersect(['abm_tn_tel', 'all'], $anm_opt))) { ?>
							<tr>
								<td colspan="2">Du bist als <b>Teilnehmer angemeldet</b> &#x2705;. Die Planung ist abgeschlossen,<br>Abmeldungen sind nur noch über den Schiffsführer möglich.</td>
							</tr>
						<?php } ?>

					<!-- segeltl, alle, TN nicht ang, geschlossen, belegt egal-->
                        <?php if (!empty(array_intersect(['anm_tn_geschl', 'all'], $anm_opt))) { ?>
							<tr>
								<td colspan="2">Die Planung ist abgeschlossen, <b>du bist nicht dabei</b> &#x1F622;,<br>Anmeldungen sind nicht mehr möglich.</td>
							</tr>
						<?php } ?>

					<!-- bereitschaft, action_type_sc GF/AF, mem_group CR/SV, no reg, action_state egal, ac_reg_state_cr/sv bereit-->
                        <?php if (!empty(array_intersect(['bereit_crsv', 'all'], $anm_opt))) { ?>
                            <tr>
                                <td colspan="2">Du bist bisher nicht angemeldet</td>
                            </tr>
							<tr>
                                <td>Du kannst hier deine <b>Crew-Bereitschaftsmeldung</b> abgeben.<br><br>
                                    Wähle aus, ob du deine Bereitschaft für die <b>Decks-Crew</b> oder die <b>Service-Crew</b>, oder beide melden möchtest.
								</td>
								<td style="vertical-align: top;">
									<form action="https://rlapp.schummel.de/api/rlreg" method="POST">
										<div style="display: flex; align-items: flex-start;">
											<!-- Checkbox-Container -->
											<div style="display: flex; flex-direction: column; gap: 2px; margin-right: 20px;">
											  <label style="white-space: nowrap;">
                                                  <input type="checkbox" class="checkbox" name="groups[]" value="cr"> Crew</label>
											  <label style="white-space: nowrap;">
                                                  <input type="checkbox" class="checkbox" name="groups[]" value="sv"> Service</label>
											</div>
											<input type="hidden" name="webid" value="<?php echo $webId ?>">
											<input type="hidden" name="actionid" value="<?php echo $actionId ?>">
											<input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
											<input type="hidden" name="anm_pot" value="<?php echo $anm_opt ?>">
											<button type="submit" class="custom-table-btn">Melden</button>
										</div>
									</form>
								</td>
							</tr>
						<?php } ?>

                        <?php if (!empty(array_intersect(['bereit_cr', 'all'], $anm_opt))) { ?>
                            <tr>
                                <td colspan="2">Du bist bisher nicht angemeldet</td>
                            </tr>
                            <tr>
                                <td>
                                    Du kannst hier deine <b>Crew-Bereitschaftsmeldung</b> für die <b>Decks-Crew</b> abgeben.<br><br>
                                </td>
                                <td>
                                    <form action="https://rlapp.schummel.de/api/rlreg" method="POST">
                                        <input type="hidden" name="groups[]" value="cr">
                                        <input type="hidden" name="webid" value="<?php echo $webId?>">
                                        <input type="hidden" name="actionid" value="<?php echo $actionId?>">
                                        <input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
                                        <input type="hidden" name="anm_opt" value="<?php echo $anm_opt ?>">
                                        <button type="submit" class="custom-table-btn">Bereitschaft melden</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if (!empty(array_intersect(['bereit_sv', 'all'], $anm_opt))) { ?>
                            <tr>
                                <td colspan="2">Du bist bisher nicht angemeldet</td>
                            </tr>
                            <tr>
                                <td>
                                    Du kannst hier deine <b>Crew-Bereitschaftsmeldung</b> für die <b>Service-Crew</b> abgeben.<br><br>
                                </td>
                                <td>
                                    <form action="https://rlapp.schummel.de/api/rlreg" method="POST">
                                        <input type="hidden" name="groups[]" value="cr">
                                        <input type="hidden" name="webid" value="<?php echo $webId?>">
                                        <input type="hidden" name="actionid" value="<?php echo $actionId?>">
                                        <input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
                                        <input type="hidden" name="anm_opt" value="<?php echo $anm_opt ?>">
                                        <button type="submit" class="custom-table-btn">Bereitschaft melden</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>


                        <!-- bereitschaft, action_type_sc VF/GF/AF, mem_group CR/SV, reg SV, action_state egal, ac_reg_state_cr/sv bereit-->
                        <?php if (!empty(array_intersect(['abm_cr', 'all'], $anm_opt))) { ?>
                            <tr>
                                <td colspan="2">Du hast Deine Bereitschaft für <b>Decks-Crew</b> gemeldet</td>
                            </tr>
                        <?php } ?>
                        <?php if (!empty(array_intersect(['abm_sv', 'all'], $anm_opt))) { ?>
                            <tr>
                                <td colspan="2">Du hast Deine Bereitschaft für <b>Service-Crew</b> gemeldet</td>
                            </tr>
                        <?php } ?>
                        <?php if (!empty(array_intersect(['abm_crsv', 'all'], $anm_opt))) { ?>
                            <tr>
                                <td colspan="2">Du hast Deine Bereitschaft für <b>Decks-Crew</b> und <b>Service-Crew</b> gemeldet</td>
                            </tr>
                        <?php } ?>
                        <?php if (!empty(array_intersect(['abm_sv','abm_cr','abm_crsv','all'], $anm_opt))) { ?>
							<tr>
								<td>Du kannst Deine Bereitschaft wieder abmelden.<br>Eventuelle Gäste werden auch mit abgemeldet!!</td>
								<td>
									<form action="https://rlapp.schummel.de/api/rlreg" method="POST">
										<input type="hidden" name="webid" value="<?php echo $webId?>">
										<input type="hidden" name="actionid" value="<?php echo $actionId?>">
										<input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME']?>">
										<input type="hidden" name="abmeldung" value="1">
										<input type="hidden" name="anm_opt" value="<?php echo $anm_opt ?>">
										<button type="submit" class="custom-table-btn">Abmelden</button>
									</form>
								</td>
							</tr>
						<?php } ?>

					<!-- bereitschaft, action_type_sc VF/GF/AF, mem_group CR/SV, reg CR, action_state egal, ac_reg_state_cr geplant-->
                        <?php if (!empty(array_intersect(['abm_cr_tel', 'all'], $anm_opt))) { ?>
							<tr>
								<td colspan="2">Du bist für die <b>Decks-Crew angenommen</b>. &#x2705; Die Crew-Planung ist abgeschlossen,<br>Abmeldungen sind nur noch über den Schiffsführer möglich.</td>
							</tr>
						<?php } ?>

					<!-- bereitschaft, action_type_sc VF/GF/AF, mem_group CR/SV, reg CR/SV abgelehnt, action_state egal, ac_reg_state_cr geplant-->
                        <?php if (!empty(array_intersect(['fertig_crsv', 'all'], $anm_opt))) { ?>
							<tr>
								<td colspan="2">Die Crew- und Service-Planung sind abgeschlossen, <b>du bist nicht dabei.</b> &#x1F622;<br>Anmeldungen sind nicht mehr möglich.</td>
							</tr>
						<?php } ?>

                        <?php if (!empty(array_intersect(['abm_sv_tel', 'all'], $anm_opt))) { ?>
							<tr>
								<td colspan="2">Du bist für die <b>Service-Crew angenommen</b> &#x2705;. Die Planung ist abgeschlossen,<br>Abmeldungen sind nur noch über den Schiffsführer möglich.</td>
							</tr>
						<?php } ?>

                        <?php if (!empty(array_intersect(['crsv_abgl', 'all'], $anm_opt))) { ?>
							<tr>
								<td colspan="2">Die Service-Planung ist abgeschlossen, <b>du bist nicht dabei.</b> &#x1F622;<br>Anmeldungen sind nicht mehr möglich.</td>
							</tr>
						<?php } ?>

                        <?php if (!empty(array_intersect(['gst_list', 'all'], $anm_opt))) { ?>
                            <style>
                                .guest-container {
                                    display: grid;
                                    grid-template-columns: auto auto auto;
                                    gap: 1px;
                                    background-color: #ddd;
                                }
                                .row {
                                    display: contents;
                                    gap: 1px;
                                }
                                .cell {
                                    background: white;
                                    padding: 10px;
                                }
                            </style>
                            <tr>
								<td colspan="2">Deine angefragten oder angenommen Gäste sind:
                                    <div class="guest-container">

                                        <div class="row">
                                            <div class="cell">Karl-Heinz<br>Familie</div>
                                            <div class="cell">angefragt</div>
                                            <div class="cell">
                                                <form action="/action1">
                                                    <button type="submit" class="custom-table-btn">abmelden</button>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="cell">Lieselotte<br>Freund</div>
                                            <div class="cell">angefragt</div>
                                            <div class="cell">
                                                <form action="/action2">
                                                    <button type="submit" class="custom-table-btn">abmelden</button>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="cell">Friedrich Wilhelm Ganzlanger Name<br>Interessent</div>
                                            <div class="cell">angenommen</div>
                                            <div class="cell">
                                                <form action="/action2">
                                                    <button type="submit" class="custom-table-btn">abmelden</button>
                                                </form>
                                            </div>
                                        </div>

                                    </div>
							    </td>
							</tr>
						<?php } ?>

                        <?php if (!empty(array_intersect(['anfr_gst', 'all'], $anm_opt))) { ?>
						<tr>
							<td>Hier kannst Du Deiner Anmeldung einen weiteren Gast hinzufügen.<br><br>
							Die Teilnahme ist zunächst nur angefragt. Die Annahme oder Ablehnung wird Dir per Email gesendet, und wird dann hier auch sichtbar.</td>
							<td>
								<style>
									.form-container {
										display: grid;
										grid-template-columns: auto auto; /* Zwei Spalten */
										grid-template-rows: auto auto auto; /* Drei Zeilen */
										gap: 5px; /* Abstand zwischen den Zellen */
										align-items: center; /* Zentrierung der Elemente vertikal */
									}
									.form-row {
										display: contents; /* Sicherstellen, dass die Kinder innerhalb des Grids bleiben */
									}
									label {
										grid-column: 1; /* Erste Spalte */
										margin-right: 10px; /* Abstand zwischen Label und Eingabefeld */
									}
									input, button, select {
										grid-column: 2; /* Zweite Spalte */
									}
                                    .styled-input {
                                        border: 2px solid gray; /* Grauer Rand */
                                        border-radius: 10px; /* Runde Ecken */
                                        padding: 5px;
                                        outline: none;
                                        transition: background-color 0.3s ease; /* Glatter Übergang für den Hover-Effekt */
                                    }
                                    .styled-input:hover {
                                        background-color: #f0f0f0; /* Hintergrundfarbe beim Hover */
                                    }
                                    .styled-input:focus {
                                        box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); /* Optional: Schatten beim Fokus für besseren Fokus-Effekt */
                                    }
                                </style>
								<form action="https://rlapp.schummel.de/api/rlreg" method="POST">
									<div class="form-container">
										<div class="form-row">
											<label for="gst_name">Name:</label>
											<input type="text" class="styled-input" id="gst_name" name="gst_name" size="12" max="20">
										</div>
										<div class="form-row">
											<label for="gst_bezug">Bezug:</label>
                                            <select id="gst_bezug" name="gst_bezug">
                                                <option value="Familie">Familie</option>
                                                <option value="Freund">Freund</option>
                                                <option value="Interessent">Interessent</option>
                                            </select>
										</div>
										<div class="form-row">
											<input type="hidden" name="webid" value="<?php echo $webId ?>">
											<input type="hidden" name="actionid" value="<?php echo $actionId ?>">
											<input type="hidden" name="host" value="<?php echo $_SERVER['SERVER_NAME'] ?>">
											<input type="hidden" name="abmeldung" value="1">
											<input type="hidden" name="anm_pot" value="<?php echo $anm_opt ?>">
											<button type="submit" class="custom-table-btn">Anfragen</button>
										</div>
									</div>
								</form>
							</td>
						</tr>
						<?php } ?>

                        <?php if (!empty(array_intersect(['no_anm', 'all'], $anm_opt))) { ?>
							<tr>
								<td colspan="2">Anmeldungen sind im Moment nicht möglich.</td>
							</tr>
						<?php } ?>

                        <?php if (!empty(array_intersect(['bereit_link', 'all'], $anm_opt))) { ?>
                            <tr>
                                <td colspan="2">Du hast für diese Fahrt eine Crew-Bereitschaft angemeldet. Bearbeite diese bitte in der <a href="/intern/bereitschaft">Liste der Crew-Bereitschaftsmeldungen</a></td>
                            </tr>
                        <?php } ?>

                        <?php if (!empty(array_intersect(['segeltn_link', 'all'], $anm_opt))) { ?>
                            <tr>
                                <td colspan="2">Du hast Dich für diese Fahrt als mitfahrendes Mitglied angemeldet. Bearbeite diese bitte in der <a href="/intern/segeltermine-neu">Segelterminliste</a></td>
                            </tr>
                        <?php } ?>

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
                                <td style="width: 30%;text-align: right;"><b>Schiffsführer</b></td>
                                <td>noch offen</td>
                            </tr>
                            <tr>
                                <td style="width: 30%;text-align: right; vertical-align: top;"><b>Decks-Crew</b></td>
                                <td> 10 Bereitschaftsmeldungen</td>
                            </tr>
                        <?php } ?>
                        <?php if ($ac['action_type_sc'] == 'vf' or $ac['action_type_sc'] == 'gf') { ?>
                            <tr>
                                <td style="width: 30%;text-align: right; vertical-align: top;"><b>Service-Crew</b></td>
                                <td>3 Bereitschaftsmeldungen</td>
                            </tr>
                        <?php } ?>
                        <?php if ($ac['action_type_sc'] == 'vf') { ?>
                            <tr>
                                <td style="width: 30%;text-align: right; vertical-align: top;"><b>Mitglieder</b></td>
                                <td>12 mitfahrende Mitglieder<br>plus 3 auf der Warteliste</td>
                            </tr>
                            <tr>
                                <td style="width: 30%;text-align: right; vertical-align: top;"><b>Gäste</b></td>
                                <td>4 (maximal 6)</td>
                            </tr>
                        <?php } ?>
                        <?php if ($ac['action_type_sc'] == 'vt' or $ac['action_type_sc'] == 'sc') { ?>
                            <tr>
                                <td style="width: 30%;text-align: right; vertical-align: top;"><b>Teilnehmer</b></td>
                                <td>12 Anmeldungen</td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </td>
        </tr>
    </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('rl_details', 'rl_details');

?>
