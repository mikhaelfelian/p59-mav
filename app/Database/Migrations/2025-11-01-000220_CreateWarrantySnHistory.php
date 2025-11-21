<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating warranty_sn_history table to track serial number replacement history
 * This file represents the Migration for CreateWarrantySnHistory.
 */
class CreateWarrantySnHistory extends Migration
{
    public function up()
    {
        // Check if table already exists
        if (in_array('warranty_sn_history', $this->db->listTables())) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'claim_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'FK to warranty_claim.id',
            ],
            'old_sn_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'FK to item_sn.id (old serial)',
            ],
            'new_sn_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'FK to item_sn.id (new serial)',
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'comment'    => 'e.g., "replacement"',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true); // Primary key
        $this->forge->addKey('claim_id');
        $this->forge->addKey('old_sn_id');
        $this->forge->addKey('new_sn_id');

        // Create table
        $this->forge->createTable('warranty_sn_history', false, [
            'ENGINE'    => 'InnoDB',
            'CHARSET'   => 'utf8mb4',
            'COLLATE'   => 'utf8mb4_general_ci',
            'COMMENT'   => 'Stores serial number replacement history for warranty claims',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('warranty_sn_history', true);
    }
}

