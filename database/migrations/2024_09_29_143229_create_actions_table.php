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
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
			$table->string('action_type', length: 50);
			$table->date('action_date')->index();
			$table->time('crew_start_at');
			$table->time('crew_end_at');
			$table->time('action_start_at')->index();
			$table->time('action_end_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
