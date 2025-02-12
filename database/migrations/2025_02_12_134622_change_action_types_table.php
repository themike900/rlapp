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
        Schema::table('action_types', function (Blueprint $table) {
            $table->renameColumn('groups','groups_max');
        });

        Schema::table('action_types', function (Blueprint $table) {
            $table->string('groups',20)->default('');
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
