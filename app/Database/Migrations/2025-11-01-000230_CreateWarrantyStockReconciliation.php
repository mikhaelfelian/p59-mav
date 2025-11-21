<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating warranty_stock_reconciliation table to track stock movements during warranty replacements
 * This file represents the Migration for CreateWarrantyStockReconciliation.
 */
class CreateWarrantyStockReconciliation extends Migration
{
    public function up()
    {
        // Check if table already exists
        if (in_array('warranty_stock_reconciliation', $this->db->listTables())) {
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
            'from_store_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Store/warehouse ID (source)',
            ],
            'to_store_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Store/warehouse ID (destination)',
            ],
            'sn_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'FK to item_sn.id',
            ],
            'reconciled_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true); // Primary key
        $this->forge->addKey('claim_id');
        $this->forge->addKey('sn_id');

        // Create table
        $this->forge->createTable('warranty_stock_reconciliation', false, [
            'ENGINE'    => 'InnoDB',
            'CHARSET'   => 'utf8mb4',
            'COLLATE'   => 'utf8mb4_general_ci',
            'COMMENT'   => 'Stores stock reconciliation logs for warranty claim replacements',
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('warranty_stock_reconciliation', true);
    }
}

