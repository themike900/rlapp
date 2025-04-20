<?php

namespace App\Http\Controllers\Pdfs;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FartenlistePdf extends Controller
{
    public function generatePdf($webId)
    {

        $regs = DB::table('action_members')
            ->join('actions', 'actions.id', '=', 'action_members.action_id')
            ->where('action_members.web_id', $webId)
            //->whereIn('action_members.reg_state', ['of', 'gs'])
            ->whereDate('action_date', '>=', date('Y-m-d'))
            ->orderBy('actions.action_date')
            ->orderBy('action_start_at')
            ->get();

        Log::debug($regs);

        $member = DB::table('members')
            ->where('webid', $webId)
            ->first();

        $pdf = Pdf::loadView('layouts.fahrtenliste', compact('regs', 'member')); // Blade-Template in PDF umwandeln
        return $pdf->stream('fahrtenliste.pdf'); // Direkt im Browser anzeigen
    }
}
