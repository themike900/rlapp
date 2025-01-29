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
        Schema::table('action_members', function (Blueprint $table) {
            $table->string('reg_state', 4)->change();
        });

        Schema::table('action_states', function (Blueprint $table) {
            $table->string('sc',4)->change();
            // $table->timestamps();
        });

        Schema::table('action_types', function (Blueprint $table) {
            $table->string('sc',4)->change();
        });

        Schema::table('actions', function (Blueprint $table) {
            $table->string('action_type_sc',4)->change();
            $table->string('action_state_sc',4)->change();
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->string('sc',4)->change();
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
