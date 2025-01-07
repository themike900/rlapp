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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->integer('webid')->nullable()->unsigned()->index();
            $table->string('name')->index();
            $table->string('firstname')->index();
            $table->string('nickname')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('action_types')->default('vf,uf,vt,mv,ar,abr');
            $table->string('groups')->default('');
            $table->timestamp('last_access')->nullable();
            $table->timestamps();
        });

        DB::table('members')->insert([
            [
                'webid' => '100',
                'name' => 'Schummel',
                'firstname' => 'Michael',
                'nickname' => 'Michael S',
                'email' => 'michael@schummel.de',
                'action_types' => 'vf,gf,af,uf,vt,sc,mv,vs,ar,abr,wa',
                'groups' => 'ab,vs',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'webid' => '101',
                'name' => 'Test1',
                'firstname' => 'Tester1',
                'nickname' => 'Test 1',
                'email' => 'test@rlapp.schummel.de',
                'action_types' => 'vf,af,vt,mv,ar,abr',
                'groups' => '',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'webid' => '102',
                'name' => 'Test2',
                'firstname' => 'Tester2',
                'nickname' => 'Test 2',
                'email' => 'test2@rlapp.schummel.de',
                'action_types' => 'vf,gf,af,uf,vt,mv,ar,abr',
                'groups' => 'cr',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'webid' => '103',
                'name' => 'Test3',
                'firstname' => 'Tester3',
                'nickname' => 'Test 3',
                'email' => 'test3@rlapp.schummel.de',
                'action_types' => 'vf,gf,af,vt,mv,ar,abr',
                'groups' => 'sv',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'webid' => '104',
                'name' => 'Test4',
                'firstname' => 'Tester4',
                'nickname' => 'Test 4',
                'email' => 'test4@rlapp.schummel.de',
                'action_types' => 'vf,gf,af,uf,vt,mv,ar,abr,wa,vs',
                'groups' => 'kp,vs,sc,wa',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
