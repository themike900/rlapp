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
        DB::table('action_types')->truncate();

        Schema::table('action_types', function (Blueprint $table) {
            $table->string('web_list', length: 4)->change();
        });


        DB::table('action_types')->insert([
            ['order' => 1, 'name' => 'Vereinsfahrt',           'sc' => 'vf',  'web_list' => 'slbm', 'groups' => '{"cr": 6, "se": 2, "mf": 25}'],
            ['order' => 2, 'name' => 'Ausbildungsfahrt',       'sc' => 'af',  'web_list' => 'bm', 'groups' => '{"cr": 15}'],
            ['order' => 3, 'name' => 'Gästefahrt extern',      'sc' => 'gfx', 'web_list' => 'bm', 'groups' => '{"cr": 10, "se": 2}'],
            ['order' => 4, 'name' => 'Gästefahrt Mitglied',    'sc' => 'gfm', 'web_list' => 'bm', 'groups' => '{"cr": 10, "se": 2}'],
            ['order' => 5, 'name' => 'Übungsfahrt',            'sc' => 'uf',  'web_list' => '',   'groups' => '{"cr": 15}'],
            ['order' => 6, 'name' => 'Vereinstreffen',         'sc' => 'vt',  'web_list' => 'vl', 'groups' => '{"tn": 100}'],
            ['order' => 7, 'name' => 'Shanty-Chor',            'sc' => 'sc',  'web_list' => 'vl', 'groups' => '{"tn": 30}'],
            ['order' => 8, 'name' => 'Aufriggen',              'sc' => 'ar',  'web_list' => 'vl', 'groups' => '{"tn": 100}'],
            ['order' => 9, 'name' => 'Abriggen',               'sc' => 'abr', 'web_list' => 'vl', 'groups' => '{"tn": 100}'],
            ['order' => 10, 'name' => 'Winterarbeit',          'sc' => 'wa',  'web_list' => 'vl', 'groups' => '{"tn": 20}'],
            ['order' => 11, 'name' => 'Mitgliederversammlung', 'sc' => 'mv',  'web_list' => 'vl', 'groups' => '{"tn": 100}'],
            ['order' => 12, 'name' => 'Vereinsreise',          'sc' => 'ft',  'web_list' => 'vl', 'groups' => '{"tn": 100}'],
            ['order' => 13, 'name' => 'Vorstandssitzung',      'sc' => 'vs',  'web_list' => '',   'groups' => '{"tn": 10}'],

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
