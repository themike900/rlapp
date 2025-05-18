<?php

namespace App\Livewire\Emails;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SentEmails extends Component
{
    public $sentEmails = Collection::class;
    public $selectedEmail = null;
    public $selectedId = null;

    public $subject = null;
    public $text = null;
    public $receiver = null;
    public $sent_at  = null;


    public function showEmail($id): void
    {
        Log::debug("--- SentEmails.showEmail: {$id}");
        $this->selectedId = $id;
        //$this->render();
    }

    public function render()
    {
        $this->sentEmails = DB::table("sent_emails")
            ->orderBy("created_at","desc")
            ->limit(50)
            ->get();

        Log::debug("--- SentEmails.render: {$this->selectedId}");
        if($this->selectedId){
            $this->selectedEmail = DB::table("sent_emails")->where("id",$this->selectedId)->first();
        } else {
            $this->selectedEmail = DB::table("sent_emails")->orderBy("created_at","desc")->first();
            $this->selectedId = $this->selectedEmail->id ?? null;
        }
        $this->subject = $this->selectedEmail->subject ?? null;
        $this->text = $this->selectedEmail->text ?? null;
        $this->receiver = $this->selectedEmail->receiver ?? null;
        //$this->sent_at = $this->selectedEmail->created_at ?? null;
        $this->sent_at = Carbon::createFromFormat('Y-m-d H:i:s', $this->selectedEmail->created_at)->isoFormat('dd DD.MM. HH:mm:ss') ?? null;

        return view('livewire.emails.sent-emails');
    }
}
