<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWindowDaysAndStackableToAgentCashbackRule extends Migration
{
    public function up()
    {
        $fields = [
            'window_days' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'after'      => 'rule_type',
            ],
            'is_stackable' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'cashback_amount',
            ],
        ];

        $this->forge->addColumn('agent_cashback_rule', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('agent_cashback_rule', ['window_days', 'is_stackable']);
    }
}


