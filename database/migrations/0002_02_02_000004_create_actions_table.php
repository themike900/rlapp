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
            $table->bigIncrements('id');
            $table->timestamps();

            $table->string('action_type', length: 50);
			$table->date('action_date')->index();
			$table->string('crew_start_at');
			$table->string('crew_end_at');
			$table->string('action_start_at')->index();
			$table->string('action_end_at');

            $table->string('action_type_sc');
            $table->string('action_state_sc');
            //$table->foreign('action_type_sc')->references('sc')->on('action_types');
            //$table->foreign('action_state_sc')->references('sc')->on('action_states');

            $table->string('reason')->nullable();
            $table->string('applicant_name')->nullable();
            $table->string('applicant_email')->nullable();
            $table->string('applicant_phone')->nullable();
            $table->tinyText('invoice_address')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('invoice_amount')->nullable();
            $table->string('guest_count')->nullable();
            $table->string('catering_info')->nullable();
            $table->string('ice_info')->nullable();
            $table->string('crew_supply')->nullable();
            $table->tinyText('additional_info')->nullable();
        });

        DB::table('actions')->insert([
            [
                'action_type' => 'Vereinsfahrt',
                'action_type_sc' => 'vf',
                'action_state_sc' => 'of',
                'action_date' => '2025-05-01',
                'crew_start_at' => '15:00',
                'crew_end_at' => '19:00',
                'action_start_at' => '15:00',
                'action_end_at' => '19:00',
                'created_at' => now(),
                'updated_at' => now(),
                'reason' => null,
                'applicant_name' => null,
                'applicant_email' => null,
                'applicant_phone' => null,
                'invoice_address' => null,
                'contact_name' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'invoice_amount' => null,
                'guest_count' => null,
                'catering_info' => null,
                'ice_info' => null,
                'crew_supply' => null,
                'additional_info' => null,
            ],
            [
                'action_type' => 'Übungsfahrt',
                'action_type_sc' => 'uf',
                'action_state_sc' => 'of',
                'action_date' => '2025-05-02',
                'crew_start_at' => '15:00',
                'crew_end_at' => '19:00',
                'action_start_at' => '15:00',
                'action_end_at' => '19:00',
                'created_at' => now(),
                'updated_at' => now(),
                'reason' => null,
                'applicant_name' => null,
                'applicant_email' => null,
                'applicant_phone' => null,
                'invoice_address' => null,
                'contact_name' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'invoice_amount' => null,
                'guest_count' => null,
                'catering_info' => null,
                'ice_info' => null,
                'crew_supply' => null,
                'additional_info' => null,
            ],
            [
                'action_type' => 'Ausbildungsfahrt',
                'action_type_sc' => 'af',
                'action_state_sc' => 'of',
                'action_date' => '2025-05-03',
                'crew_start_at' => '15:00',
                'crew_end_at' => '19:00',
                'action_start_at' => '15:00',
                'action_end_at' => '19:00',
                'created_at' => now(),
                'updated_at' => now(),
                'reason' => null,
                'applicant_name' => null,
                'applicant_email' => null,
                'applicant_phone' => null,
                'invoice_address' => null,
                'contact_name' => null,
                'contact_email' => null,
                'contact_phone' => null,
                'invoice_amount' => null,
                'guest_count' => null,
                'catering_info' => null,
                'ice_info' => null,
                'crew_supply' => null,
                'additional_info' => null,
            ],
            [
                'action_type' => 'Gästefahrt',
                'action_type_sc' => 'gf',
                'action_state_sc' => 'of',
                'action_date' => '2025-05-04',
                'crew_start_at' => '13:00',
                'crew_end_at' => '19:00',
                'action_start_at' => '14:00',
                'action_end_at' => '18:00',
                'created_at' => now(),
                'updated_at' => now(),
                'reason' => '70. Geburtstag',
                'applicant_name' => 'Max Mustermann',
                'applicant_email' => 'max.mustermann@gmail.com',
                'applicant_phone' => '0170 555555',
                'invoice_address' => 'Musterstr. 10, 1245 Berlin',
                'contact_name' => 'Karl-Heinz Mustermann',
                'contact_email' => 'karl.heinz@gmail.com',
                'contact_phone' => '0170 444444',
                'invoice_amount' => '1400',
                'guest_count' => '25',
                'catering_info' => 'Butter-Lidner liefert',
                'ice_info' => 'VSaW',
                'crew_supply' => 'Crew ist eingeladen',
                'additional_info' => '',
            ],
        ]);
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
