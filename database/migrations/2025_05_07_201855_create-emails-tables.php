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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('template')->index()->unique();
            $table->string('subject');
            $table->text('text');
        });

        Schema::create('sent_emails', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('receiver')->index();
            $table->string('subject');
            $table->text('text');
            $table->string('pdf_name')->nullable();
            $table->binary('pdf')->nullable();
        });

        DB::table('email_templates')->insert([
            ['template' => 'crew-zusage',       'subject' => 'Royal-Louise Crew-Zusage', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'crew-absage',       'subject' => 'Royal-Louise Crew-Absage', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'service-zusage',    'subject' => 'Royal-Louise Service-Zusage', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'service-absage',    'subject' => 'Royal-Louise Service-Absage', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'tn-zu-crew',        'subject' => 'Royal-Louise Ummeldung zur Crew', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'tn-to-service',     'subject' => 'Royal-Louise Ummeldung zum Service', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'wl-zu-tn',          'subject' => 'Royal-Louise Ummeldung zu Teilnehmern', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'wl-zu-crew',        'subject' => 'Royal-Louise Ummeldung zur Crew', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'del-wl',            'subject' => 'Royal-Louise Löschung aus der Warteliste', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'gast-angenommen',   'subject' => 'Royal-Louise Annahme Gast', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'gast-abgelehnt',    'subject' => 'Royal-Louise Ablehnung Gast', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'sf-erinnerung',     'subject' => 'Royal-Louise Erinnerung Crew-Planung', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'sv-erinnerung',     'subject' => 'Royal-Louise Erinnerung Service-Planung', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
            ['template' => 'sf-fahrtenblatt',   'subject' => 'Royal-Louise Fahrtenblatt', 'text' => 'Hallo', 'created_at' => now(), 'updated_at' => now()],
         ]);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('sent_emails');
        //
    }
};
