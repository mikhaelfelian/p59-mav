<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAgentCashbackRuleTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'agent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'rule_type' => [
                'type'       => 'ENUM',
                'constraint' => ['cashback', 'akumulasi'],
                'default'    => 'cashback',
            ],
            'min_transaction' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'cashback_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'end_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('agent_id');
        $this->forge->addKey('rule_type');
        $this->forge->addKey('is_active');
        $this->forge->createTable('agent_cashback_rule', true);
    }

    public function down()
    {
        $this->forge->dropTable('agent_cashback_rule', true);
    }
}


