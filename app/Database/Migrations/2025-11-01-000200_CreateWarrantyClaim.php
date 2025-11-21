<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating warranty_claim table to track warranty claim submissions
 * This file represents the Migration for CreateWarrantyClaim.
 */
class CreateWarrantyClaim extends Migration
{
    public function up()
    {
        // Check if table already exists
        if (in_array('warranty_claim', $this->db->listTables())) {
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
            'old_sn_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'FK to item_sn.id',
            ],
            'issue_reason' => [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
            ],
            'photo_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'system_validated' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'system_validation_note' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'routed_store_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Store/warehouse ID',
            ],
            'store_approved' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
            ],
            'store_note' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending','invalid','rejected','approved','replaced'],
                'null'       => false,
                'default'    => 'pending',
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
        ]);

        $this->forge->addKey('id', true); // Primary key
        $this->forge->addKey('agent_id');
        $this->forge->addKey('old_sn_id');
        $this->forge->addKey('routed_store_id');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');

        // Create table
        $this->forge->createTable('warranty_claim', false, [
            'ENGINE'    => 'InnoDB',
            'CHARSET'   => 'utf8mb4',
            'COLLATE'   => 'utf8mb4_general_ci',
            'COMMENT'   => 'Stores warranty claim submissions and their status',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('warranty_claim', true);
    }
}

