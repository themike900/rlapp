<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('action_types', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('order');
            $table->string('name');
            $table->json('prams');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_types');
    }
};
