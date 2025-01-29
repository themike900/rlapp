<?php

function fahrtenliste($atts){

    // Typ der aufgerufenen Liste aus dem shortcode-Parameter, oder aus den user-Meta-Daten holen
    $list_type = $atts['typ'] ?? '' ;
    $user_id = get_current_user_id();
    $meta_weblist = get_user_meta($user_id, 'weblist', true);
    //print_r("list_type: {$list_type}");

    // List-Type prio: 1. aus shortcode-Parameter, 2. aus Meta-Daten, 3. Default-Wert
    if (empty($list_type)) {
        $list_type = $meta_weblist ?? 'Segeltermine';
    } else {
        update_user_meta($user_id, 'weblist', $list_type);
    }

    // Daten für Versendung der web-Nutzerdaten zusammenstellen
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

    // Webnutzer an API senden und Listendaten zurückbekommen
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

    // wenn web
    $msg = '';
    if (curl_errno($ch)) {
        $msg = 'cURL-Fehler: ' . curl_error($ch);
        $data = [];
    } else {
        $data = json_decode($response, true);
        if (empty($data)) {
            $msg = "<p>Für dich ist diese Liste derzeit leer</p>";
        }
    }
    curl_close($ch);

    ob_start();
?>
    <style>
        .tb { border-collapse: collapse; width:100%; font-size: 90% }
        .tb th, .tb td { padding: 5px; border-bottom: solid 1px #777; border-top: solid 1px #777; }
        .tb td:nth-child(1) {width: 12%}
        .tb td:nth-child(2) {width: 20%}
        .tb td:nth-child(3) {width: 13%}
        .tb td:nth-child(4) {width: 13%}
        .tb td:nth-child(5) {width: 12%}
        .tb tr:hover { background-color: #EEEEEE; }
    </style>

    <?php echo $msg; ?>
    <table class='tb'>
        <?php foreach($data as $f) { ?>
        <tr id='<?php echo $f['action_id'] ?>' tabindex='-1'>
            <td><b>Datum</b><br><?php echo $f['action_date'] ?></td>
            <td><b>Anlass</b><br><?php echo $f['action_name'] ?></td>
            <td><b><?php echo $f['start_at_text'] ?></b><br><?php echo $f['start_at'] ?> Uhr</td>
            <td><b><?php echo $f['end_at_text'] ?></b><br><?php echo $f['end_at'] ?> Uhr</td>
            <td><b>Status</b><br><?php echo $f['action_state_name'] ?></td>
            <td><b>Teilnahme</b><br><?php echo $f['reg_state_name'] ?></td>
            <td><b>Details</b><br><a href="/intern/details?id=<?php echo $f['action_id'] ?>">hier klicken</a></td>
        </tr>
        <?php } ?>

    </table>

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

<?php
    return ob_get_clean();
}
add_shortcode('rl_fahrtenliste', 'fahrtenliste');

?>
