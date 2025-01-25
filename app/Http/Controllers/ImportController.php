<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\MembersImport;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function importMembers(Request $request) {

        $request->validate(['file' => 'required|mimes:xlsx,xls']);

        Excel::import(new MembersImport, $request->file('file'));

        return back()->with('success', 'Mitglieder-Import erfolgreich.');
    }
    //
}
