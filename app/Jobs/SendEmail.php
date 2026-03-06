<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use function Psy\debug;

class SendEmail implements ShouldQueue
{
    use Queueable;

    public string $web_id;
    public string $templateName;
    public array $data;

    /**
     * Create a new job instance.
     */
    public function __construct($web_id, $templateName, $data)
    {
        $this->templateName = $templateName;
        $this->web_id = $web_id;
        $this->data = $data;
        Log::debug("Job: $this->web_id, $this->templateName");
    }

    /**
     * Execute the job.
     *  array Receiver, text parameters
     */
    public function handle(): void
    {
        Log::debug(' ---- SendEmail.handle ----------------------------------');

        // member, Empfänger holen
        $member = DB::table('members')->where('webid',$this->web_id)->first();
        if (empty($member)) { return; }
        if (empty($member->email)) { return; }

        // Schiffsführer holen
        $captain = DB::table('action_members')
            ->where('action_id',$this->data['action_id'])
            ->where('group','sf')
            ->join('members','members.webid','=','action_members.web_id')
            ->first();
        $sf_name = (!empty($captain)) ? $captain->firstname : 'noch offen';
        $sf_mobile = (!empty($captain)) ? $captain->mobile : '';

        // Email-Template holen
        $template= DB::table('email_templates')
            ->where('template', $this->templateName)
            ->first();
        if (empty($template)) { return; }

        // Fahrt holen
        $action = DB::table('actions')->where('id', $this->data['action_id'])->first();

        // Daten vorbereiten
        $action_date = Carbon::createFromFormat('Y-m-d', $action->action_date)->isoFormat('dddd DD.MM.');
        $template->subject = "$template->subject für $action_date";
        $gst_name = (!empty($this->data['gst_name'])) ? $this->data['gst_name'] : '';

        // Array fürs Rendern auffüllen
        $data = array_merge($this->data, [
            'action_name' => $action->action_name,
            'action_date' => $action_date,
            'crew_start_at' => $action->crew_start_at,
            'action_start_at' => $action->action_start_at,
            'firstname' => $member->firstname,
            'captain' => $sf_name,
            'gst_name' => $gst_name,
            'sf_mobile' => $sf_mobile,
            'cancel_reason' => $action->cancel_reason
        ]);
        Log::debug("data: " . print_r($data, true));

        // Text rendern
        $emailText = Blade::render($template->text,$data);

        // Absender festlegen
        $sender = 'Royal-Louise Planung';
        $senderEmail = 'planung@royal-louise.de';

        Log::debug("Email $this->templateName an $member->fullname für $action->action_name am $action->action_date Betreff: $template->subject");

        // Email senden
        Mail::send([], [], function ($message) use ($sender, $senderEmail, $template, $emailText, $member) {
            $message->from($senderEmail, $sender)
                ->to($member->email, $member->fullname)
                // ->bcc('test@rlapp.schummel.de')
                ->subject($template->subject)
                //->attachData($pdf->output(), "fahrtenblatt.pdf")
                ->html($emailText);

        });

        // Versand in DB eintragen
        DB::table('sent_emails')->insert([
            'receiver' => "$member->fullname ($member->email)",
            'subject' => $template->subject,
            'text' => $emailText,
            'created_at' => now(),
            'updated_at' => now()
        ]);

    }
}
