<?php

function fahrtenliste($atts){

    $list_type = $atts['typ'] ?? '' ;
    $user_id = get_current_user_id();
    $meta_weblist = get_user_meta($user_id, 'weblist', true);
    //print_r("list_type: {$list_type}");

    if (empty($list_type)) {
        $list_type = $meta_weblist ?? 'Segeltermine';
    } else {
        update_user_meta($user_id, 'weblist', $list_type);
    }

    $flstyle = <<<ST
    <style>
        .tb { border-collapse: collapse; width:100%; font-size: 90% }
        .tb th, .tb td { padding: 5px; border-bottom: solid 1px #777; border-top: solid 1px #777; }
		.tb td:nth-child(1) {width: 12%}
		.tb td:nth-child(2) {width: 20%}
		.tb td:nth-child(3) {width: 13%}
		.tb td:nth-child(4) {width: 13%}
		.tb td:nth-child(5) {width: 12%}
		.tb td:nth-child(6) {width: 8%}
        .tb tr:hover { background-color: #EEEEEE; }
    </style>
ST;

    $fjs =<<<JS
    <script type="text/javascript">
        function setFocusOnRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.focus();
                row.scrollIntoView( {behavior: "smooth", block: "center"} );
            }
        }
        const url = new URL(window.location.href);
        const params = new URLSearchParams(url.search);
        const actionId = params.get('id');
        window.onload = function() {
            setFocusOnRow( actionId );
        }
    </script>
JS;

    $web_id = um_profile_id();
    $postdata = array(
        "webid" => um_profile_id(),
        "firstname" => um_user('first_name'),
        "name" => um_user('last_name'),
        "email" => um_user('user_email'),
        "nickname" => um_user('nickname'),
        "list_type" => $list_type
    );
    $json_pdata = json_encode($postdata);
    //print_r($json_pdata);

    $api_url = 'rlapp.schummel.de/api/list';
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    //curl_setopt($ch, MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json_pdata)
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_pdata);

    $response = curl_exec($ch);
    //print_r($response);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        $out = 'cURL-Fehler: ' . $error;
    } else {
        $data = json_decode($response, true);
        if (empty($data)) {
            $a = 0;
            $out = "<p>Für dich ist diese Liste derzeit leer</p>";
        } else {
            $out = $flstyle."<table class='tb'>";
            foreach($data as $f){
                $out .= "<tr id='{$f['action_id']}' tabindex='-1'>";
                $out .= "  <td><b>Datum</b><br>{$f['action_date']}</td>";
                $out .= "  <td><b>Anlass</b><br>{$f['action_name']}</td>";
                $out .= "  <td><b>{$f['start_at_text']}</b><br>{$f['start_at']} Uhr</td>";
                $out .= "  <td><b>{$f['end_at_text']}</b><br>{$f['end_at']} Uhr</td>";
                $out .= "  <td><b>Status</b><br>{$f['action_state_name']}</td>";
                $out .= "  <td><b>Teilnahme</b><br>{$f['reg_state_name']}</td>";
                $out .= "  <td><b>Details</b><br><a href=\"/intern/details?id={$f['action_id']}\">hier klicken</a></td>";
                $out .= "</tr>";
            }
            $out .= "</table>";
            $out .= $fjs;
        }
    }

    curl_close($ch);

    return $out;
}
add_shortcode('rl_fahrtenliste', 'fahrtenliste');

?>
