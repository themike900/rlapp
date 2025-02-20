<?php

namespace App\Imports;

use App\Models\Action;
use Illuminate\Database\Eloquent\Model;
use App\Models\Member;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
//use function Psy\debug;

class MembersImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     * @return Member
     */
    public function model(array $row): Member
    {
        //Log::debug(json_encode($row));

        $groups = str_replace('Deckscrew',        'cr', $row['mannschaft']);
        $groups = str_replace('Servicecrew',      'sv', $groups);
        $groups = str_replace('Schiffsführer',    'sf', $groups);
        $groups = str_replace('Winterarbeit',     'wa', $groups);
        $groups = str_replace('Vorstand',         'vs', $groups);
        $groups = str_replace('Shanty-Chor',      'sh', $groups);
        $groups = str_replace('Verwaltung',       'vw', $groups);
        $groups = str_replace('Deckcrew Reserve', 'crr', $groups);
        $groups = str_replace('Toppsgast',        'tg', $groups);
        $groups = str_replace('Trainee',          'tr', $groups);
        $groups = str_replace(' ',                '',   $groups);
        $groups = ($row['aufentern'] == 'Ja') ? $groups . ',ae' : $groups;
        $groups = ($groups == '-') ? '' : $groups;

        // ist der Member mit der mem_id schon da?
        $member = Member::where('mem_id', $row['nr'])->first();

        if (empty($member)) {
            $member = Member::where('email', $row['e_mail'])->first();
        }

        // Wenn ja, nur groups überschreiben
         if ($member) {
             $member->mem_id = $row['nr'];
             $member->groups = $groups;
             $member->save();

             // Wemm nein, neuen Datensatz anlegen
         } else {
             $member = Member::create([
                 'mem_id'     => $row['nr'] ?? null,
                 'firstname'  => $row['vorname'] ?? '',
                 'name'       => $row['nachname'] ?? '',
                 'nickname'   => $row['vorname'] . ' ' . substr($row['nachname'], 0,1 )?? '',
                 'email'      => $row['e_mail'] ?? null,
                 'groups'     => $groups,
             ]);
         }

        return $member;
    }
}
