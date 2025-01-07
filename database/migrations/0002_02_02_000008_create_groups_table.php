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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sc')->index();
            $table->string('action_types');
            //$table->timestamps();
        });

        DB::table('groups')->insert([
            ['name' => 'Mitglied',     'sc' => 'mg', 'action_types' => 'vf,af,vt,mv,ar,abr'],
            ['name' => 'Crew',         'sc' => 'cr', 'action_types' => 'gf,uf'],
            ['name' => 'Service',      'sc' => 'sv', 'action_types' => 'gf'],
            ['name' => 'Ausbilder',    'sc' => 'ab', 'action_types' => 'gf,uf'],
            ['name' => 'Kapitän',      'sc' => 'kp', 'action_types' => 'gf,uf'],
            ['name' => 'Vorstand',     'sc' => 'vs', 'action_types' => 'vs'],
            ['name' => 'Shanty',       'sc' => 'sh', 'action_types' => 'sc'],
            ['name' => 'Winterarbeit', 'sc' => 'wa', 'action_types' => 'wa'],
            ['name' => 'Mitfahrer',    'sc' => 'mf', 'action_types' => ''],
            ['name' => 'Lernende',     'sc' => 'le', 'action_types' => ''],
            ['name' => 'Teilnehmer',   'sc' => 'tn', 'action_types' => ''],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
