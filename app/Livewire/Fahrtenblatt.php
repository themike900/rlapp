<?php

namespace App\Livewire;

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
        Log::debug('Fahrtenblatt opened');
        $pdf = Pdf::loadView('livewire.fahrtenblatt');
        //Log::debug($pdf->output());

        $response = new Response($pdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="fahrtenblatt.pdf"');
        return $response;

        //header('Content-Type: application/pdf');
        //header('Content-Disposition: inline; filename="fahrtenblatt.pdf"');
        //echo $pdf->output();
        //exit;

        //return response()->make($pdf->output(), 200, [
        //    'Content-Type' => 'application/pdf',
        //    'Content-Disposition' => 'inline; filename="fahrtenblatt.pdf"'
        //]);
    }

    public function render(): View
    {
        return view('livewire.fahrtenblatt');
    }

}
