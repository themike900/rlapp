<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // alles löschen
        DB::table('action_types')->delete();

        // neuen Inhalt laden
        DB::table('action_types')->insert([
            ['order' => 1, 'name' => 'Vereinsfahrt',           'sc' => 'vf',  'groups_max' => '{"gst": 5, "pers": 30, "tnwl": 1}', 'web_list' => 'slbm', 'groups' => 'tn,cr,sv,sf,gst'],
            ['order' => 2, 'name' => 'Ausbildungsfahrt',       'sc' => 'af',  'groups_max' => '{"pers": 30}',                      'web_list' => 'slbm', 'groups' => 'tn,cr,sv,sf'],
            ['order' => 3, 'name' => 'Gästefahrt extern',      'sc' => 'gfx', 'groups_max' => '{"pers": 30}',                      'web_list' => 'bm',   'groups' => 'tn,cr,sv,sf'],
            ['order' => 4, 'name' => 'Gästefahrt Mitglied',    'sc' => 'gfm', 'groups_max' => '{"pers": 30}',                      'web_list' => 'bm',   'groups' => 'tn,cr,sv,sf'],
            ['order' => 5, 'name' => 'Übungsfahrt',            'sc' => 'uf',  'groups_max' => '{"pers": 30}',                      'web_list' => '',     'groups' => ''],
            ['order' => 6, 'name' => 'Vereinstreffen',         'sc' => 'vt',  'groups_max' => '{"tn": 100}',                       'web_list' => 'vl',   'groups' => 'tn,gst'],
            ['order' => 7, 'name' => 'Shanty-Chor',            'sc' => 'sc',  'groups_max' => '{"tn": 30}',                        'web_list' => 'vl',   'groups' => 'sh'],
            ['order' => 8, 'name' => 'Aufriggen',              'sc' => 'afr', 'groups_max' => '{"tn": 100}',                       'web_list' => 'vl',   'groups' => 'tn'],
            ['order' => 9, 'name' => 'Abriggen',               'sc' => 'abr', 'groups_max' => '{"tn": 100}',                       'web_list' => 'vl',   'groups' => 'tn'],
            ['order' => 10, 'name' => 'Winterarbeit',          'sc' => 'wa',  'groups_max' => '{"tn": 20}',                        'web_list' => 'vl',   'groups' => 'wa'],
            ['order' => 11, 'name' => 'Mitgliederversammlung', 'sc' => 'mv',  'groups_max' => '{"tn": 100}',                       'web_list' => 'vl',   'groups' => 'tn'],
            ['order' => 12, 'name' => 'Vereinsreise',          'sc' => 'vr',  'groups_max' => '{"tn": 50, "tnwl": 1}',             'web_list' => 'vl',   'groups' => 'tn,gst'],
            ['order' => 13, 'name' => 'Vorstandssitzung',      'sc' => 'vs',  'groups_max' => '{"tn": 20}',                        'web_list' => '',     'groups' => 'vs'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
