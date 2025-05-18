<?php

namespace App\Livewire\Emails;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SentEmails extends Component
{
    public $sentEmails = Collection::class;
    public $selectedEmail = null;
    public $selectedId = null;

    public $subject = null;
    public $text = null;
    public $receiver = null;


    public function render()
    {
        $this->sentEmails = DB::table("sent_emails")
            ->orderBy("created_at","desc")
            ->limit(50)
            ->get();

        if($this->selectedId){
            $this->selectedEmail = DB::table("sent_emails")->where("id",$this->selectedId)->first();
        } else {
            $this->selectedEmail = DB::table("sent_emails")->orderBy("created_at","desc")->first();
            $this->selectedId = $this->selectedEmail->id;
        }
        $this->subject = $this->selectedEmail->subject;
        $this->text = $this->selectedEmail->text;
        $this->receiver = $this->selectedEmail->receiver;

        return view('livewire.emails.sent-emails');
    }
}
