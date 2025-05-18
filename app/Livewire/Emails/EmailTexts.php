<?php

namespace App\Livewire\Emails;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Livewire\Component;

class EmailTexts extends Component
{
    public $subject;
    public $text;
    public $selectedTemplate;
    public $templateId = 1;

    public $templates;
    public $saved = false;

    public function saveTemplate(): void
    {
        DB::table('email_templates')->where('id', $this->templateId)->update([
            'subject' => $this->subject,
            'text' => $this->text,
        ]);
        $this->saved = true;
    }

    public function changeTemplateId(): void
    {
        $this->saved = false;
    }
    public function render()
    {

        $this->templates = DB::table('email_templates')->get();

        $this->selectedTemplate = DB::table('email_templates')->where('id',$this->templateId)->first();
        $this->subject = $this->selectedTemplate->subject;
        $this->text = $this->selectedTemplate->text;


        return view('livewire.emails.email-texts');
    }
}
