<?php

namespace App\Imports;

use App\Models\Member;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

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

        // ist der Member mit der mv_id schon da?
        $member = Member::where('mv_id', $row['nr'])->first();

        // wenn die mv_id noch nicht existiert
        if (empty($member)) {
            // wenn der zu importierende Kontakt eine Email-Adresse hat
            if ($row['e_mail'] != '-') {
                // suche ein Mitglied mit dieser Email-Adresse und diesem Vornamen
                $member = Member::where('email', $row['e_mail'])->where('firstname',$row['vorname'])->first();
                Log::debug("import member, gefunden mit email '{$row['e_mail']}' und mv_id '{$row['nr']}'");
            } else {
                // wenn zu importierendes Mitglied kein Email-Adresse hat
                // suche Mitglied mit Vorname und Nachname
                $member = Member::where('name', $row['nachname'])->where('firstname',$row['vorname'])->first();
                Log::debug("import member, gefunden mit nachname '{$row['nachname']}' und mv_id '{$row['nr']}'");
            }
        }

        // Wenn Mitglied schon vorhanden, nur groups und mv_id überschreiben
         if ($member) {

             if ($member->mv_id != $row['nr'] or $member->groups != $groups) {

                 Log::debug("import member ändern: $member->mv_id => {$row['nr']}, $member->groups => {$groups}");

                 $member->mv_id = $row['nr'];
                 $member->groups = $groups;
                 $member->save();

             }

             // Wenn nein, neuen Datensatz anlegen
         } else {

             Log::debug('insert: '.print_r($row, true));

             $member = Member::create([
                 'mv_id'      => $row['nr'] ?? null,
                 'firstname'  => $row['vorname'] ?? '',
                 'name'       => $row['nachname'] ?? '',
                 'nickname'   => '',
                 'email'      => $row['e_mail'] ?? '',
                 'groups'     => $groups ?? '',
             ]);
         }

        return $member;
    }
}
