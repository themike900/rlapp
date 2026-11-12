<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApiMemberLinkTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::reconnect('sqlite');
        $this->createApiTables();
    }

    public function test_existing_webid_is_not_replaced_when_email_matches(): void
    {
        DB::table('members')->insert($this->member(42, 'eva@example.test'));

        $this->postJson('/api/list', $this->listPayload(99, 'eva@example.test'))
            ->assertOk()
            ->assertExactJson([]);

        $this->assertSame(42, (int) DB::table('members')->value('webid'));
    }

    public function test_empty_webid_is_still_linked_on_first_visit(): void
    {
        DB::table('members')->insert($this->member(null, 'eva@example.test'));

        $this->postJson('/api/list', $this->listPayload(42, 'eva@example.test'))->assertOk();

        $this->assertSame(42, (int) DB::table('members')->where('email', 'eva@example.test')->value('webid'));
    }

    public function test_old_webid_above_10000_is_still_migrated(): void
    {
        DB::table('members')->insert($this->member(10050, 'eva@example.test'));
        DB::table('action_members')->insert([
            'web_id' => 10050,
            'action_id' => 1,
            'group' => 'tn',
            'reg_state' => 'ang',
        ]);

        $this->postJson('/api/list', $this->listPayload(42, 'eva@example.test'))->assertOk();

        $this->assertSame(42, (int) DB::table('members')->value('webid'));
        $this->assertSame(42, (int) DB::table('action_members')->value('web_id'));
    }

    public function test_crew_signup_without_crew_group_is_ignored(): void
    {
        DB::table('members')->insert($this->member(5, 'eva@example.test', ''));
        $this->seedOpenAction();

        $this->post('/api/rlreg', $this->regPayload(5, 'bereit_cr'))
            ->assertRedirect('https://rlweb.test/intern/details?id=1&anm=true');

        $this->assertSame(0, DB::table('action_members')->count());
    }

    public function test_crew_signup_with_crew_group_still_works(): void
    {
        DB::table('members')->insert($this->member(5, 'eva@example.test', 'cr'));
        $this->seedOpenAction();

        $this->post('/api/rlreg', $this->regPayload(5, 'bereit_cr'))
            ->assertRedirect('https://rlweb.test/intern/details?id=1&anm=true');

        $row = DB::table('action_members')->first();
        $this->assertSame('cr', $row->group);
        $this->assertSame('br', $row->reg_state);
    }

    public function test_participant_signup_still_works(): void
    {
        DB::table('members')->insert($this->member(5, 'eva@example.test', ''));
        $this->seedOpenAction();

        $this->post('/api/rlreg', $this->regPayload(5, 'anm_tn'))->assertRedirect();

        $this->assertSame('tn', DB::table('action_members')->value('group'));
    }

    public function test_guest_is_not_attached_to_another_actions_registration(): void
    {
        $this->seedOpenAction(1);
        $this->seedOpenAction(2);
        DB::table('action_members')->insert([
            'id' => 3,
            'web_id' => 5,
            'action_id' => 1,
            'group' => 'tn',
            'reg_state' => 'ang',
        ]);

        $this->post('/api/gstreg', [
            'host' => 'rlweb.test',
            'action_id' => 2,
            'reg_id' => 3,
            'gst_name' => 'Gast',
            'gst_bezug' => 'Familie',
        ])->assertRedirect();

        $this->assertSame(0, DB::table('guests')->count());
    }

    public function test_guest_request_for_the_same_action_still_works(): void
    {
        $this->seedOpenAction(1);
        DB::table('action_members')->insert([
            'id' => 3,
            'web_id' => 5,
            'action_id' => 1,
            'group' => 'tn',
            'reg_state' => 'ang',
        ]);

        $this->post('/api/gstreg', [
            'host' => 'rlweb.test',
            'action_id' => 1,
            'reg_id' => 3,
            'gst_name' => 'Gast',
            'gst_bezug' => 'Familie',
        ])->assertRedirect('https://rlweb.test/intern/details?id=1&anm=true');

        $this->assertSame('Gast', DB::table('guests')->value('name'));
    }

    public function test_guest_delete_does_not_remove_a_guest_of_another_action(): void
    {
        $this->seedOpenAction(1);
        $this->seedOpenAction(2);
        DB::table('guests')->insert([
            'id' => 9,
            'reg_id' => 3,
            'gst_action_id' => 1,
            'gst_state' => 'angefragt',
            'name' => 'Gast',
        ]);

        $this->post('/api/gstreg', [
            'host' => 'rlweb.test',
            'action_id' => 2,
            'guest_id' => 9,
            'gst_state' => 'angefragt',
            'abmeldung' => 1,
        ]);

        $this->assertSame(1, DB::table('guests')->count());
    }

    public function test_api_requires_the_token_only_when_it_is_configured(): void
    {
        config(['services.rlweb.token' => 'tok']);

        $this->postJson('/api/list', $this->listPayload(1, 'eva@example.test'))->assertForbidden();

        $this->postJson('/api/list', $this->listPayload(1, 'eva@example.test'), ['X-RL-Token' => 'tok'])
            ->assertOk();

        $this->seedOpenAction();
        $this->post('/api/rlreg', $this->regPayload(5, 'anm_tn'))->assertForbidden();
        $this->post('/api/rlreg', $this->regPayload(5, 'anm_tn') + ['rl_token' => 'tok'])
            ->assertRedirect();
    }

    private function listPayload(int $webid, string $email): array
    {
        return [
            'webid' => $webid,
            'firstname' => 'Eva',
            'name' => 'Beispiel',
            'email' => $email,
            'list_type' => 'Segeltermine',
        ];
    }

    private function regPayload(int $webid, string $opt): array
    {
        return [
            'webid' => $webid,
            'actionid' => 1,
            'host' => 'rlweb.test',
            'anm_opt' => $opt,
        ];
    }

    private function member($webid, string $email, string $groups = 'tn'): array
    {
        return [
            'webid' => $webid,
            'name' => 'Beispiel',
            'firstname' => 'Eva',
            'email' => $email,
            'groups' => $groups,
            'nickname' => 'Eva B',
            'fullname' => 'Eva Beispiel',
        ];
    }

    private function seedOpenAction(int $id = 1): void
    {
        DB::table('actions')->insert([
            'id' => $id,
            'action_date' => '2026-12-01',
            'action_state_sc' => 'of',
            'action_type_sc' => 'vt',
            'ac_max_pers' => 30,
            'ac_max_guests' => 5,
            'crew_supply' => '',
            'catering_info' => '',
            'ice_info' => '',
            'ac_reg_state_tn' => 'tnon',
            'ac_with_wl' => 0,
        ]);
    }

    private function createApiTables(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->integer('webid')->nullable();
            $table->string('name')->nullable();
            $table->string('firstname')->nullable();
            $table->string('nickname')->nullable();
            $table->string('fullname')->nullable();
            $table->string('email')->nullable();
            $table->string('groups')->nullable();
            $table->timestamp('last_access')->nullable();
            $table->string('reg_error')->nullable();
            $table->timestamps();
        });
        Schema::create('action_members', function (Blueprint $table) {
            $table->id();
            $table->integer('web_id')->nullable();
            $table->integer('action_id')->nullable();
            $table->string('group')->nullable();
            $table->string('reg_state')->nullable();
            $table->string('reg_error')->nullable();
            $table->timestamps();
        });
        Schema::create('actions', function (Blueprint $table) {
            $table->id();
            $table->date('action_date')->nullable();
            $table->string('action_state_sc')->nullable();
            $table->string('action_type_sc')->nullable();
            $table->integer('ac_max_pers')->nullable();
            $table->integer('ac_max_guests')->nullable();
            $table->string('crew_supply')->nullable();
            $table->string('catering_info')->nullable();
            $table->string('ice_info')->nullable();
            $table->string('ac_reg_state_tn')->nullable();
            $table->integer('ac_with_wl')->nullable();
            $table->timestamps();
        });
        Schema::create('action_types', function (Blueprint $table) {
            $table->id();
            $table->string('sc')->nullable();
            $table->string('web_list')->nullable();
            $table->string('groups')->nullable();
            $table->string('groups_max')->nullable();
        });
        Schema::create('list_actions', function (Blueprint $table) {
            $table->integer('action_id')->nullable();
            $table->date('action_date')->nullable();
            $table->string('action_start_at')->nullable();
            $table->string('action_state_sc')->nullable();
            $table->string('action_type_sc')->nullable();
        });
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->integer('reg_id')->nullable();
            $table->integer('gst_action_id')->nullable();
            $table->string('gst_state')->nullable();
            $table->string('name')->nullable();
            $table->string('reference')->nullable();
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('app_events', function (Blueprint $table) {
            $table->id();
            $table->string('text')->nullable();
            $table->integer('web_id')->nullable();
            $table->integer('action_id')->nullable();
            $table->timestamps();
        });
        DB::table('action_types')->insert([
            'sc' => 'vt',
            'web_list' => 'vl',
            'groups' => '{}',
            'groups_max' => '{}',
        ]);
    }
}
