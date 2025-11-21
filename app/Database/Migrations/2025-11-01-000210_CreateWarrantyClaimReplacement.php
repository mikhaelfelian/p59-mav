<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating warranty_claim_replacement table to track replacement serial numbers
 * This file represents the Migration for CreateWarrantyClaimReplacement.
 */
class CreateWarrantyClaimReplacement extends Migration
{
    public function up()
    {
        // Check if table already exists
        if (in_array('warranty_claim_replacement', $this->db->listTables())) {
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
            'new_sn_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'FK to item_sn.id',
            ],
            'replaced_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
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
        $this->forge->addKey('claim_id');
        $this->forge->addKey('new_sn_id');

        // Create table
        $this->forge->createTable('warranty_claim_replacement', false, [
            'ENGINE'    => 'InnoDB',
            'CHARSET'   => 'utf8mb4',
            'COLLATE'   => 'utf8mb4_general_ci',
            'COMMENT'   => 'Stores replacement serial numbers for warranty claims',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('warranty_claim_replacement', true);
    }
}

