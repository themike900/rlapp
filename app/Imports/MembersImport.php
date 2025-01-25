<?php

namespace App\Imports;

use Illuminate\Database\Eloquent\Model;
use App\Models\Members;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use function Psy\debug;

class MembersImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return Model|Members|null
     */
    public function model(array $row): Model|Members|null
    {
        Log:debug($row);
        /*$existingMember = Members::where('mem_id', $row['Nr.'])->first();

        $groups = str_repalce('Deckscrew',        'cr', $row['Mannschaft']);
        $groups = str_repalce('Servicecrew',      'sv', $groups);
        $groups = str_repalce('Schiffsführer',    'kp', $groups);
        $groups = str_repalce('Winterarbeit',     'wa', $groups);
        $groups = str_repalce('Vorstand',         'vs', $groups);
        $groups = str_repalce('Shanty-Chor',      'cr', $groups);
        $groups = str_repalce('Verwaltung',       'vw', $groups);
        $groups = str_repalce('Deckcrew Reserve', 'crr', $groups);

        if ($existingMember) {
            $existingMember->update([
                'groups' => $groups,
            ]);
            return $existingMember;
        }*/

        return new Members([
            'firstname' => $row['Vorname'],
            'name'     => $row['Nachname'],
            'email'    => $row['E-Mail'],
            //'mem_id'   => $row['Nr.'],
            'groups'   => $groups,
        ]);
    }
}
