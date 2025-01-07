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
			$table->timestamp('action_date')->change();
			$table->string('crew_start_at', 10)->default('00:00')->change();
			$table->string('crew_end_at', 10)->default('00:00')->change();
			$table->string('action_start_at', 10)->default('00:00')->change();
			$table->string('action_end_at', 10)->default('00:00')->change();
		});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actions', function (Blueprint $table) {
			$table->date('action_date')->change();
			$table->time('crew_start_at')->change();
			$table->time('crew_end_at')->change();
			$table->time('action_start_at')->change();
			$table->time('action_end_at')->change();
        });
    }
};
