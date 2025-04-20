<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Fahrtenliste </title>
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
            right: 20px;
            font-size: 12px;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: auto;
            margin-top: 20px;
        }
        td {
            font-size: 12px;
            border: 1px solid #ccc;
            padding: 10px;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="date-box">
        <span>{{ date('d.m.Y') }}</span>
    </div>

    <h3 class="title">ROYAL-LOUISE Fartenliste für {{ $member->firstname }}</h3>

    <p>No of regs: {{ count($regs) }}</p>

    <table>
        <tbody>
        @foreach($regs as $reg)
            <tr>
                <td><b>{{ $reg->action_name }}</b></td>
                <td>{{ $reg->action_date }}</td>
                <td>{{ $reg->group }}</td>
                <td>{{ $reg->reg_state }}</td>
                <td>{{ $reg->action_state_sc }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
</body>
</html>
