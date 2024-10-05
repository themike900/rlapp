<?php

namespace Database\Seeders;

use App\Models\ActionState;
use App\Models\ActionType;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create(); ------------------------------

        User::factory()->create([
            'name' => 'User Test1',
            'email' => 'test@rlapp.schummel.de',
            'password' => Hash::make('test1234')
        ]);

        User::factory()->create([
            'name' => 'User Test2',
            'email' => 'test2@rlapp.schummel.de',
            'password' => Hash::make('test1234')
        ]);

        User::factory()->create([
            'name' => 'User Test3',
            'email' => 'test3@rlapp.schummel.de',
            'password' => Hash::make('test1234')
        ]);

        // ActionState table -------------------------------------

        ActionState::create([
            'name' => 'in Vorbereitung',
            'order' => 1
        ]);

        ActionState::create([
            'name' => 'bereit',
            'order' => 2
        ]);

        ActionState::create([
            'name' => 'offen',
            'order' => 3
        ]);

        ActionState::create([
            'name' => 'geschlossen',
            'order' => 4
        ]);

        ActionState::create([
            'name' => 'durchgeführt',
            'order' => 5
        ]);

        ActionState::create([
            'name' => 'abgeschlossen',
            'order' => 6
        ]);

        // ActionTypes table ---------------------------------------

        ActionType::create([
            'name' => 'Vereinsfahrt',
            'order' => 1,
            'params' => json_encode(['Mitfahrer' => 25, 'Crew' => 6, 'Service' => 2])
        ]);

        ActionType::create([
            'name' => 'Gästefahrt',
            'order' => 2,
            'params' => json_encode(['Crew' => 6, 'Service' => 2])
        ]);

        ActionType::create([
            'name' => 'Ausbildungsfahrt',
            'order' => 3,
            'params' => json_encode(['Ausbilder' => 3, 'Lernende' => 12 ])
        ]);

        ActionType::create([
            'name' => 'Übungsfahrt',
            'order' => 4,
            'params' => json_encode(['Crew' => 15 ])
        ]);

        ActionType::create([
            'name' => 'Mitgliederversammlung',
            'order' => 5,
            'params' => json_encode(['Teilnehmer' => 100 ])
        ]);

        ActionType::create([
            'name' => 'Vorstandssitzung',
            'order' => 6,
            'params' => json_encode(['Teilnehmer' => 10 ])
        ]);
    }
}
