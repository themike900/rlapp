<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CronAuthTest extends TestCase
{
    public function test_cron_without_token_does_nothing(): void
    {
        config(['app.cron_secret' => 's3cret']);

        $this->get('/cron/schedule')->assertForbidden();
        $this->get('/cron/queue')->assertForbidden();
        $this->get('/cron/schedule/wrong')->assertForbidden();
        $this->get('/cron/queue/wrong')->assertForbidden();
        $this->get('/api/cron')->assertForbidden();
        $this->get('/api/cron?token=wrong')->assertForbidden();
    }

    public function test_cron_with_empty_secret_is_rejected(): void
    {
        config(['app.cron_secret' => '']);

        $this->get('/cron/schedule/anything')->assertForbidden();
        $this->get('/api/cron?token=anything')->assertForbidden();
    }

    public function test_schedule_runs_only_with_the_secret(): void
    {
        config(['app.cron_secret' => 's3cret']);
        Artisan::shouldReceive('call')->once()->with('schedule:run')->andReturn(0);

        $this->get('/cron/schedule/s3cret')->assertOk()->assertSee('OK');
    }

    public function test_api_cron_closes_open_trips_when_the_token_matches(): void
    {
        config(['app.cron_secret' => 's3cret']);
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->date('action_date')->nullable();
            $table->string('action_state_sc')->nullable();
            $table->string('action_type_sc')->nullable();
        });
        DB::table('actions')->insert([
            'action_date' => now()->addDay()->toDateString(),
            'action_state_sc' => 'of',
            'action_type_sc' => 'vf',
        ]);

        $this->get('/api/cron?token=s3cret')->assertOk()->assertSee('1');
        $this->assertSame('gs', DB::table('actions')->value('action_state_sc'));
    }
}
