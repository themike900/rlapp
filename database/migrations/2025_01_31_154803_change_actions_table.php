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
        Schema::table('actions', function (Blueprint $table) {
            $table->string('ac_reg_state_tn', 6)->after('action_state_sc');
            $table->string('ac_reg_state_cr', 6)->after('action_state_sc');
            $table->string('ac_reg_state_sv', 6)->after('action_state_sc');
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
