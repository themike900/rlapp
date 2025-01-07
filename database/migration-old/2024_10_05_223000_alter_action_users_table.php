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
        Schema::table('action_user', function (Blueprint $table) {
            $table->foreignId('user_id')->change();
            $table->foreignId('action_id')->change();

            //$table->foreign('user_id')->references('id')->on('users');
            //$table->foreign('action_id')->references('id')->on('actions');

        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
