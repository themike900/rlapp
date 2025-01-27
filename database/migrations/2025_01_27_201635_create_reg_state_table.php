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
        Schema::create('reg_state', function (Blueprint $table) {
            $table->id();
            $table->integer('order');
            $table->string('name', 20);
            $table->string('sc', 4);
            $table->string('grp', 4);
        });

        DB::table('reg_state')->insert([
            ['order' => 11, 'grp' => 'cr', 'sc' => 'br', 'name' => 'gemeldet'],
            ['order' => 12, 'grp' => 'cr', 'sc' => 'cr', 'name' => 'Deck'],
            ['order' => 13, 'grp' => 'cr', 'sc' => 'abgl', 'name' => 'nein'],
            ['order' => 14, 'grp' => 'cr', 'sc' => 'wl', 'name' => 'Warteliste'],
            ['order' => 21, 'grp' => 'sv', 'sc' => 'br', 'name' => 'gemeldet'],
            ['order' => 22, 'grp' => 'sv', 'sc' => 'sv', 'name' => 'Service'],
            ['order' => 23, 'grp' => 'sv', 'sc' => 'abgl', 'name' => 'nein'],
            ['order' => 24, 'grp' => 'sv', 'sc' => 'wl', 'name' => 'Warteliste'],
            ['order' => 31, 'grp' => 'tn', 'sc' => 'ang', 'name' => 'gemeldet'],
            ['order' => 32, 'grp' => 'tn', 'sc' => 'wl', 'name' => 'Warteliste'],
            ['order' => 33, 'grp' => 'tn', 'sc' => 'abgl', 'name' => 'abgelehnt'],
            ['order' => 41, 'grp' => 'gst', 'sc' => 'agf', 'name' => 'angefragt'],
            ['order' => 42, 'grp' => 'gst', 'sc' => 'agn', 'name' => 'angenommen'],
            ['order' => 51, 'grp' => 'sf', 'sc' => 'sf', 'name' => 'Schiffsf.'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reg_state');
    }
};
