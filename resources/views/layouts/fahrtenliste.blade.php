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
            font-size: 20px;
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
            font-size: 14px;
            border: 1px solid #ccc;
            padding: 5px;
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

    @php
        $currentWeek = null;
    @endphp

    @foreach($actions as $action)

        @if ($currentWeek != $action->week)
            @php
                $currentWeek = $action->week;
            @endphp

            @if(!$loop->first)
                </tbody>
                </table>
            @endif
            <table>
                <tbody>
        @endif
        <tr>
            <td style="width: 15%"><b>{{ $action->action_date }}</b></td>
            <td style="width: 15%">{{ $action->start_time }}</td>
            <td style="width: 40%"><b>{{ $action->action_name }}</b></td>
            @if($action->reg_color == 'green')
                <td style="width: 30%; background-color: lightgreen">{!! $action->reg_state_text !!}</td>
            @elseif($action->reg_color == 'red')
                <td style="width: 30%; background-color: lightcoral">{!! $action->reg_state_text !!}</td>
            @else
                <td style="width: 30%; background-color: white">{!! $action->reg_state_text !!}</td>
            @endif

        </tr>
    @endforeach
    @if(!empty($actions))
                </tbody>
            </table>
    @endif
</div>
</body>
</html>
