<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW list_actions AS
                SELECT
                    `actions`.`id`              AS `action_id`,
                    `actions`.`action_date`     AS `action_date`,
                    `actions`.`crew_start_at`   AS `crew_start_at`,
                    `actions`.`crew_end_at`     AS `crew_end_at`,
                    `actions`.`action_start_at` AS `action_start_at`,
                    `actions`.`action_end_at`   AS `action_end_at`,
                    `actions`.`reason`          AS `reason`,
                    `actions`.`action_type_cs`  AS `action_type`,
                    `a_s`.`name`                AS `action_state_name`,
                    `a_t`.`name`                AS `action_type_name`
                FROM
                    actions
                    LEFT JOIN action_states a_s
                        ON actions.action_state_sc = a_s.sc
                    LEFT JOIN action_types a_t
                        ON actions.action_type_sc = a_t.sc
                WHERE
                    actions.action_date >= CURRENT_DATE()
                ORDER BY actions.action_date
                ;
        ");
    }
};
