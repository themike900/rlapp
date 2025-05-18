<?php

namespace App\Jobs;

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
        Log::debug("{$this->web_id} {$this->templateName}");
    }

    /**
     * Execute the job.
     *  array Receiver, text parameters
     */
    public function handle(): void
    {
        Log::debug(' ---- SendEmail.handle');
        //Log::debug("{$this->web_id} {$this->templateName}");
        $member = DB::table('members')->where('webid',$this->web_id)->first();
        if (empty($member)) { return; }
        //Log::debug('member '. $member->id);

        $captain = DB::table('action_members')
            ->where('action_id',$this->data['action_id'])
            ->where('group','sf')
            ->join('members','members.webid','=','action_members.web_id')
            ->first();

        Log::debug('template '. $this->templateName);

        $template= DB::table('email_templates')->where('template', $this->templateName)->first();
        //Log::debug(print_r($template, true));
        if (empty($template)) { return; }
        Log::debug("subject {$template->subject}");


        Log::debug("action {$this->data['action_id']}");
        $action = DB::table('actions')->where('id', $this->data['action_id'])->first();
        $data = array_merge($this->data, [
            'action_name' => $action->action_name,
            'action_date' => $action->action_date,
            'crew_start_at' => $action->crew_start_at,
            'firstname' => $member->firstname,
            'captain' => $captain->firstname
        ]);

        $sender = 'Royal-Louise-Planung';
        //$senderEmail = 'planung@royal-louise.de';
        $senderEmail = 'test@rlapp.schummel.de';

        Log::debug('Type template->text: '.gettype($template->text));
        $emailText = Blade::render($template->text,$data);
        Log::debug('emailText: \n'.$emailText);

        Log::debug("Email {$this->templateName} an {$member->fullname} für {$action->action_name} am {$action->action_date} ");

        Mail::send([], [], function ($message) use ($sender, $senderEmail, $template, $emailText, $member) {
            $message->from($senderEmail, $sender)
                //->to($member->email, "{$member->firstname} {$member->name}")
                ->to('test@rlapp.schummel.de', $member->fullname)
                ->subject($template->subject)
                //->attachData($pdf->output(), "fahrtenblatt.pdf")
                ->html($emailText);

        });

        DB::table('sent_emails')->insert([
            //'receiver' => "$member->fullname ($member->email)",
            'receiver' => "$member->fullname ('test@rlapp.schummel.de')",
            'subject' => $template->subject,
            'text' => $emailText,
            'created_at' => now(),
            'updated_at' => now()
        ]);

    }
}
