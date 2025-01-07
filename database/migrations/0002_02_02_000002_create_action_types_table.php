<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('action_types', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('order');
            $table->string('name');
            $table->string('sc')->index();
            $table->json('groups');
            // $table->timestamps();
        });

        DB::table('action_types')->insert([
            ['order' => 1, 'name' => 'Vereinsfahrt',          'sc' => 'vf', 'groups' => '{"cr": 6, "se": 2, "mf": 25}'],
            ['order' => 2, 'name' => 'Gästefahrt',            'sc' => 'gf', 'groups' => '{"cr": 10, "se": 2}'],
            ['order' => 3, 'name' => 'Ausbildungsfahrt',      'sc' => 'af', 'groups' => json_encode( ['le' => 12, 'ab' => 3])],
            ['order' => 4, 'name' => 'Übungsfahrt',           'sc' => 'uf', 'groups' => '{"cr": 15}'],
            ['order' => 5, 'name' => 'Vereinstreffen',        'sc' => 'vt', 'groups' => '{"tn": 100}'],
            ['order' => 6, 'name' => 'Shanty-Chor',           'sc' => 'sc', 'groups' => '{"tn": 30}'],
            ['order' => 7, 'name' => 'Mitgliederversammlung', 'sc' => 'mv', 'groups' => '{"tn": 100}'],
            ['order' => 8, 'name' => 'Aufriggen',             'sc' => 'ar', 'groups' => '{"tn": 100}'],
            ['order' => 9, 'name' => 'Abriggen',              'sc' => 'abr', 'groups' => '{"tn": 100}'],
            ['order' => 10, 'name' => 'Winterarbeit',         'sc' => 'wa', 'groups' => '{"tn": 20}'],
            ['order' => 11, 'name' => 'Vorstandssitzung',     'sc' => 'vs', 'groups' => '{"tn": 10}'],
            ['order' => 12, 'name' => 'Fahrtentermin',        'sc' => 'ft', 'groups' => '{"tn": 100}']

        ]);

    }

    public function down(): void
    {
        Schema::dropIfExists('action_types');
    }
};
