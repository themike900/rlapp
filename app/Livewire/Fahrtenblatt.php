<?php

namespace App\Livewire;

use App\Models\Action;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class Fahrtenblatt extends Component
{
    protected $listeners = ['open-fahrtenblatt-pdf' => 'OpenPdf'];

    /**
     * @throws BindingResolutionException|\Throwable
     */
    public function openPdf($actionId)
    {
        $action = Action::find($actionId);
        Log::debug('Fahrtenblatt opened');
        $pdf = Pdf::loadView('livewire.fahrtenblatt', compact('action'));
        //Log::debug($pdf->output());

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="dokument.pdf"');
        echo $pdf->output();
        exit;

        //return response()->streamDownload(function () use ($pdf) {
        //        echo $pdf->stream();
        //    }, $action->action_date." Fahrtenblatt.pdf");

        //return $pdf->stream('fahrtenblatt.pdf');

    }

    public function render(): String
    {
        return view('livewire.fahrtenblatt');
    }

}
