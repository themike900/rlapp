<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('action_states', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->smallInteger('order');
            $table->string('sc')->index();
            // $table->timestamps();
        });

        DB::table('action_states')->insert([
            ['name' => 'in Vorbereitung', 'order' => 1, 'sc' => 'iv'],
            ['name' => 'bereit',          'order' => 2, 'sc' => 'br'],
            ['name' => 'offen',           'order' => 3, 'sc' => 'of'],
            ['name' => 'geschlossen',     'order' => 4, 'sc' => 'gs'],
            ['name' => 'durchgeführt',    'order' => 5, 'sc' => 'df'],
            ['name' => 'abgeschlossen',   'order' => 6, 'sc' => 'as'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('action_states');
    }
};
