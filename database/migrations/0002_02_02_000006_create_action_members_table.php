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
        Schema::create('action_members', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
			$table->foreignId('member_id')->index();
			$table->foreignId('action_id')->index();
			$table->string('group');
			$table->smallInteger('guests')->default(0);

            //$table->foreign('user_id')->references('id')->on('users');
            //$table->foreign('action_id')->references('id')->on('actions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_user');
    }
};
