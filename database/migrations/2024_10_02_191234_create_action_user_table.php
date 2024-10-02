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
        Schema::create('action_user', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
			$table->integer('user_id')->index();
			$table->integer('action_id')->index();
			$table->string('function', 20);
			$table->smallInteger('guests');
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
