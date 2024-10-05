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
            $table->bigIncrements('id')->change();
            $table->foreignId('action_type_id')->change();
            $table->foreignId('action_state_id');

            $table->string('reason');
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone');
            $table->tinyText('invoice_address');
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone');
            $table->string('invoice_amount');
            $table->string('guest_count');
            $table->string('catering_info');
            $table->string('ice_info');
            $table->string('crew_supply');
            $table->text('additional_info');


            $table->foreign('action_type_id')->references('id')->on('action_types');
            $table->foreign('action_state_id')->references('id')->on('action_states');


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
