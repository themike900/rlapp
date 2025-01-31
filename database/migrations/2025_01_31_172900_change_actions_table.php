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
            $table->string('ac_max_guests', 6)->after('reg_state_tn');
            $table->string('ac_max_pers', 6)->after('reg_state_tn');
            $table->string('ac_mit_wl', 6)->after('reg_state_tn');
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
