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
            $table->dropColumn('guests');
        });

        Schema::table('actions', function (Blueprint $table) {
            $table->renameColumn('action_type', 'action_name')->change();
            $table->boolean('ac_with_wl')->default(0)->change();
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->integer('reg_id')->nullable()->change();
            $table->integer('gst_action_id')->nullable()->after('reg_id')->index();
            $table->string('gst_state', 15)->after('gst_action_id');
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
