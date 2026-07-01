<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Blade;

class SendSms implements ShouldQueue
{
    use Queueable;

    public string $web_id;
    public string $templateName;
    public array $data;

    public function __construct($web_id, $templateName, $data)
    {
        $this->web_id = $web_id;
        $this->templateName = $templateName;
        $this->data = $data;

        Log::debug("SMS-Job: $this->web_id, $this->templateName");
    }

    public function handle(): void
    {
        Log::debug(' ---- SendSms.handle ----------------------------------');

        // Empfänger holen
        $member = DB::table('members')->where('webid', $this->web_id)->first();
        if (empty($member) || empty($member->mobile) || ($member->mobile == '-')) {
            return;
        }

        // Alles entfernen außer + und Ziffern
        $number = preg_replace('/[^0-9+]/', '', $member->mobile);

        // Schiffsführer holen
        $captain = DB::table('action_members')
            ->where('action_id',$this->data['action_id'])
            ->where('group','sf')
            ->join('members','members.webid','=','action_members.web_id')
            ->first();
        $sf_name = (!empty($captain)) ? $captain->firstname : 'noch offen';
        $sf_mobile = (!empty($captain)) ? $captain->mobile : '';

        // Wenn die Nummer mit 0 beginnt → deutsche Nummer → +49 draus machen
        if (str_starts_with($number, '0')) {
            $number = '+49' . substr($number, 1);
        }

        // Template holen
        $template = DB::table('email_templates')
            ->where('template', $this->templateName)
            ->first();
        if (empty($template)) {
            return;
        }

        // Fahrt holen
        $action = DB::table('actions')->where('id', $this->data['action_id'])->first();
        // Daten vorbereiten

        $action_date = Carbon::createFromFormat('Y-m-d', $action->action_date)->isoFormat('dddd DD.MM.');
        //$template->subject = "$template->subject für $action_date";
        //$gst_name = (!empty($this->data['gst_name'])) ? $this->data['gst_name'] : '';

        // Daten für Template
        $data = array_merge($this->data, [
            'firstname' => $member->firstname,
            'action_name' => $action->action_name,
            'action_date' => $action_date,
            'captain' => $sf_name,
            'sf_mobile' => $sf_mobile,
            'cancel_reason' => $action->cancel_reason
        ]);

        // Template rendern
        $smsText = Blade::render($template->text, $data);
        Log::debug(''.$smsText);

        Log::debug("SMS $this->templateName an $member->fullname ($number)");

        // SMS senden via smsapi.com

       $response = Http::withToken(env('SMSAPI_TOKEN'))
            ->asForm()
            ->post('https://api.smsapi.com/sms.do', [
                'to' => $number,
                'message' => $smsText,
                'from' => 'RoyalLouise',
                'format' => 'json'
            ]);

        Log::debug('SMSAPI Response: ' . $response->body());

        if ($response->failed()) {

            Log::error("SMSAPI Fehler: " . $response->body());

        } else {

            // Versand protokollieren
            DB::table('sent_emails')->insert([
                'receiver' => "$member->fullname",
                'subject' => 'SMS an '. $member->mobile,
                'text' => $smsText,
                'created_at' => now(),
                'updated_at' => now()
            ]);

        }

    }
}

