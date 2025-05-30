<?php

use App\Jobs\SendEmail;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $date = now()->format('Y-m-d');
        rename($logPath, storage_path("logs/laravel-{$date}.log"));
    }
})->daily();

Schedule::call(function () {
    $files = glob(storage_path('logs/laravel-*.log'));
    if (count($files) > 7) { // Maximal 7 Log-Dateien behalten
        array_map('unlink', array_slice($files, 0, count($files) - 7));
    }
})->daily();

// Jeden morgen um 10:00 Fahrtenblätter für den nächsten Tag versenden
Schedule::call(function () {
    Log::debug("--- Fahrtenblatt per Email senden now+1---");

    $tomorrow = now()->addDay()->format('Y-m-d');
    Log::debug("tomorrow: $tomorrow");

    $actions = DB::table('actions')
        ->whereDate('action_date', $tomorrow)
        ->get();

    Log::debug("actions: " . print_r($actions, true));

    // Für alle Fahrten des nächsten Tages
    foreach ($actions as $action) {

        // Nur wenn Fahrtenblatt noch nicht gesendet ist
        if ($action->sent_fb < 2) {

            // send Fahrtenblatt an Schiffsführer
            $reg = DB::table('action_members')
                ->where('action_id', $action->id)
                ->where('group', 'sf')
                ->first();

            if (!empty($reg)) {
                dispatch(new SendEmail($reg->web_id, 'sf-fahrtenblatt', ['action_id' => $action->id]));
                Log::debug("SendEmail: $reg->web_id sf-fahrtenblatt action_id={$action->id}" );
            }

            // send Fahrtenblatt an Service-Teilnehmer
            $sv_regs = DB::table('action_members')
                ->where('action_id', $action->id)
                ->where('group', 'sv')
                ->where('reg_state','gpl')
                ->get();

            foreach ($sv_regs as $sv_reg) {
                dispatch(new SendEmail($sv_reg->web_id, 'sv-fahrtenblatt', ['action_id' => $action->id]));
                Log::debug("SendEmail: $sv_reg->web_id sv-fahrtenblatt action_id={$action->id}" );
            }

            // set sent_fb auf Fahrtenblatt gesendet
            DB::table('actions')
                ->where('id',$action->id)
                ->update(['sent_fb' => 2]);

        }
    }
//})->everyTwoMinutes();
})->dailyAt('10:00');

// Jeden morgen um 10:10
Schedule::call(function () {
    Log::debug("--- Planungs-Erinnerung per Email senden now+10 ---");

    $tenDaysAhead = now()->addDays(10)->format('Y-m-d');
    Log::debug("tenDaysAhead: $tenDaysAhead");

    $actions = DB::table('actions')
        ->whereDate('action_date', $tenDaysAhead)
        ->get();

    Log::debug("actions: " . print_r($actions, true));
    foreach ($actions as $action) {

        // Nur wenn Erinnerung noch nicht gesendet ist und Crew-Planung noch offen ist
        if ($action->sent_fb == 0 and $action->ac_reg_state_cr == 'crbr') {

            // send Erinnerung an Schiffsführer
            $reg = DB::table('action_members')
                ->where('action_id', $action->id)
                ->where('group', 'sf')
                ->first();

            if (!empty($reg)) {

                dispatch(new SendEmail($reg->web_id, 'sf-erinnerung', ['action_id' => $action->id]));
                Log::debug("SendEmail: $reg->web_id sf-erinnerung action_id={$action->id}");

                // set sent_fb auf Erinnerung gesendet
                DB::table('actions')
                    ->where('id',$action->id)
                    ->update(['sent_fb' => 1]);

            }
        }
    }

//})->everyTwoMinutes();
})->dailyAt('10:10');


// Jeden Abend um 22:00 alle offenen und geschlossenen Fahrten auf durchgeführt setzen
Schedule::call(function () {
    Log::debug("--- Fahrten auf durchgeführt setzen ---");

    $today = now()->format('Y-m-d');
    DB::table('actions')
        ->whereDate('action_date', $today)
        ->whereIn('action_state_sc', ['of','gs'])
        ->update(['action_state_sc' => 'df']);

    $actions = DB::table('actions')
        ->whereDate('action_date', $today)
        ->where('action_state_sc', 'df')
        ->where('action_type_sc', 'gfx' )
        ->get();

    $schatzmeister = DB::table('members')
        ->where('email', 'schatzmeister@royal-louise.de')
        ->value('web_id');

    foreach ($actions as $action) {
        dispatch(new SendEmail($schatzmeister, 'sm-abrechnung', ['action_id' => $action->id,'preis' => $action->invoice_amount]));
    }

})->dailyAt('22:00');
