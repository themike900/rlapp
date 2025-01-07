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
        DB::table('action_types')->insert([
           ['order' => 9, 'name' => 'Aufriggen',     'params' => '{"Teilnehmer": 100}'],
           ['order' => 10, 'name' => 'Abriggen',     'params' => '{"Teilnehmer": 100}'],
           ['order' => 11, 'name' => 'Winterarbeit', 'params' => '{"Teilnehmer": 10}'],

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
