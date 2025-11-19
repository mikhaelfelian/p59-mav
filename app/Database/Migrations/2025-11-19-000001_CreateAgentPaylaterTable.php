<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-19
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating agent_paylater table to track agent paylater transactions
 * This file represents the Migration for CreateAgentPaylaterTable.
 */
class CreateAgentPaylaterTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if (in_array('agent_paylater', $this->db->listTables())) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'agent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'FK to agent.id',
            ],
            'sales_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'FK to sales.id (optional)',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'mutation_type' => [
                'type'       => 'ENUM',
                'constraint' => ['1', '2', '3'],
                'null'       => false,
                'collate'    => 'utf8_general_ci',
                'comment'    => '1=purchase, 2=repayment, 3=adjustment',
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'default'    => '0.00',
                'comment'    => 'Positive for hutang, negative for bayar',
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'collate'    => 'utf8_general_ci',
            ],
            'reference_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'collate'    => 'utf8_general_ci',
                'comment'    => 'Unique optional code',
            ],
        ]);

        $this->forge->addKey('id', true); // Primary key
        $this->forge->addKey('agent_id');
        $this->forge->addKey('sales_id');
        $this->forge->addKey('mutation_type');
        $this->forge->addKey('created_at');
        
        // Add unique index for reference_code (only if not null)
        $this->forge->addUniqueKey('reference_code');

        // Create table
        $this->forge->createTable('agent_paylater', false, [
            'ENGINE'    => 'InnoDB',
            'COMMENT'   => 'Stores agent paylater transactions (purchase, repayment, adjustment)',
            'COLLATE'   => 'utf8_general_ci'
        ]);

        // Add foreign key constraints
        // Note: Foreign keys are added via ALTER TABLE to avoid issues if tables don't exist yet
        $this->db->query('ALTER TABLE `agent_paylater` 
            ADD CONSTRAINT `fk_agent_paylater_agent_id` 
            FOREIGN KEY (`agent_id`) REFERENCES `agent` (`id`) 
            ON DELETE CASCADE ON UPDATE CASCADE');

        $this->db->query('ALTER TABLE `agent_paylater` 
            ADD CONSTRAINT `fk_agent_paylater_sales_id` 
            FOREIGN KEY (`sales_id`) REFERENCES `sales` (`id`) 
            ON DELETE SET NULL ON UPDATE CASCADE');
    }

    public function down()
    {
        // Drop foreign keys first
        $this->db->query('ALTER TABLE `agent_paylater` DROP FOREIGN KEY `fk_agent_paylater_agent_id`');
        $this->db->query('ALTER TABLE `agent_paylater` DROP FOREIGN KEY `fk_agent_paylater_sales_id`');
        
        $this->forge->dropTable('agent_paylater', true);
    }
}

