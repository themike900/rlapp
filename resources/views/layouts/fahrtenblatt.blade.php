<!DOCTYPE html>
<html lang="de-DE">
<head>
    <meta charset="UTF-8">
    <title>Fahrtenblatt </title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .container {
            padding: 20px;
        }
        .date-box {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 14px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 90%;
            border-collapse: collapse;
            margin: auto;
            margin-top: 20px;
        }
        td {
            width: 30%;
            font-size: 18px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        td:nth-child(1) {width: 40%}
        td:nth-child(2) {width: 60%}
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="px-0 container">

    <div class="date-box">
        <span>{{ date('d.m.Y') }}</span>
    </div>

    <h3 class="title">Fahrtenblatt ROYAL-LOUISE</h3>

    <table>
        <tbody>
        <tr>
            <td><b>Datum</b></td>
            <td>{{ $action->action_date }}</td>
        </tr>
        <tr>
            <td><b>Fahrt</b></td>
            <td>{{ $action->action_name }}</td>
        </tr>
        @if($action->action_type_sc == 'gfx')
            <tr>
                <td><b>Anlass</b></td>
                <td>{{ $action->reason ?? '' }}</td>
            </tr>
            <tr>
                <td><b>Nutzer</b></td>
                <td>{{ $action->applicant_name ?? '' }}</td>
            </tr>
            <tr>
                <td><b>Anschrift</b></td>
                <td>{{ $action->invoice_address ?? '' }}</td>
            </tr>
            <tr>
                <td><b>Email</b></td>
                <td>{{ $action->applicant_email ?? '' }}</td>
            </tr>
            <tr>
                <td><b>Telefon</b></td>
                <td>{{ $action->applicant_phone ?? '' }}</td>
            </tr>
            <tr>
                <td><b>Kontakt</b></td>
                <td>{{ $action->contact_name ?? '' }}, {{ $action->contact_phone ?? '' }}</td>
            </tr>
            <tr>
                <td><b>Kostenbeitrag</b></td>
                <td>{{ $action->invoice_amount ?? '' }}</td>
            </tr>
            <tr>
                <td><b>Anahl der Gäste</b></td>
                <td>{{ $action->guest_count ?? '' }}</td>
            </tr>
            <tr>
                <td><b>Catering</b></td>
                <td>{{ $action->catering_info ?? '' }}</td>
            </tr>
        @endif
        <tr>
            <td><b>Eis</b></td>
            <td>{{ $action->ice_info ?? '' }}</td>
        </tr>
        <tr>
            <td><b>Crew-Versorgung</b></td>
            <td>{{ $action->crew_supply ?? '' }}</td>
        </tr>
        <tr>
            <td><b>Sonstiges</b></td>
            <td>{{ $action->additional_info ?? '' }}</td>
        </tr>
        <tr>
            <td><b>Ablege - Anlegen</b></td>
            <td>{{ $action->action_start_at ?? '' }} - {{ $action->action_end_at ?? '' }}</td>
        </tr>
        <tr>
            <td><b>Crew an Bord - von Bord</b></td>
            <td>{{ $action->crew_start_at ?? '' }} - {{ $action->crew_end_at ?? '' }}</td>
        </tr>
        @if(in_array($action->action_type_sc, ['vf','gfx','uf','af']))

            <tr>
            <td><b>Besatzung</b></td>
            <td>
                <b>Schiffsführer:</b><br/>- {{ $members['captain'] }}<br/>
                <b>Crew:</b><<br/>- {!! str_replace(',', '<br/>- ', $members['crew']) !!}<br/>
                <b>Service:</b><br/>- {!! str_replace(',', '<br/>- ', $members['service']) !!}
            </td>
        </tr>
        @endif
        @if(!in_array($action->action_type_sc, ['gfx','uf','af']))
        <tr>
            <td><b>Teilnehmer</b></td>
            <td>
                {{ $members['teilnehmer'] }}
            </td>
        </tr>
        @endif
        </tbody>
    </table>
</div>
</body>
</html>
