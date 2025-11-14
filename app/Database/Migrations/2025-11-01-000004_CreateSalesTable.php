<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating sales table to store main sales transactions.
 * This file represents the Migration for CreateSalesTable.
 */
class CreateSalesTable extends Migration
{
    public function up()
    {
        // Check with db->tableExists is not valid for CI4, so use listTables
        if (in_array('sales', $this->db->listTables())) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'invoice_no' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'collate'    => 'utf8_general_ci',
            ],
            'user_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'customer_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'warehouse_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'sale_channel' => [
                'type'       => 'ENUM',
                'constraint' => ['1', '2'],
                'null'       => false,
                'collate'    => 'utf8_general_ci',
                'comment'    => '1=offline, 2=online',
            ],
            'total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'null'       => false,
                'default'    => '0.00',
            ],
            'total_payment' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'null'       => false,
                'default'    => '0.00',
            ],
            'balance_due' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'null'       => false,
                'default'    => '0.00',
            ],
            'discount_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'null'       => false,
                'default'    => '0.00',
            ],
            'tax_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'null'       => false,
                'default'    => '0.00',
            ],
            'tax_type' => [
                'type'       => 'ENUM',
                'constraint' => ['0', '1', '2'],
                'null'       => false,
                'default'    => '0',
                'collate'    => 'utf8_general_ci',
                'comment'    => '0=no tax, 1=include tax (PPN termasuk), 2=added tax (PPN ditambahkan)',
            ],
            'grand_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'null'       => false,
                'default'    => '0.00',
            ],
            'delivery_address' => [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
                'collate'    => 'utf8_general_ci',
                'comment'    => 'Delivery address for the order (can be agent registered address or custom address)',
            ],
            'note' => [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
                'collate'    => 'utf8_general_ci',
                'comment'    => 'Order notes/comments',
            ],
            'payment_status' => [
                'type'       => 'ENUM',
                'constraint' => ['0', '1', '2'],
                'null'       => false,
                'default'    => '0',
                'collate'    => 'utf8_general_ci',
                'comment'    => '0=unpaid,1=partial,2=paid'
            ],
            'settlement_time' => [
                'type'       => 'DATETIME',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Payment settlement time from gateway callback',
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
                'on_update' => 'CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addKey('id', true); // Primary key

        // Add unique index for invoice_no
        $this->forge->addUniqueKey('invoice_no');

        // Create table
        $this->forge->createTable('sales', false, [
            'ENGINE'    => 'InnoDB',
            'COMMENT'   => 'Stores main sales transactions.',
            'COLLATE'   => 'utf8_general_ci'
        ]);

        // Extra column definitions for DEFAULT and ON UPDATE expressions that are not handled by Forge/CI automatically.
        // This is necessary due to limitations for TIMESTAMP ON UPDATE clauses and DECIMAL default string formats.
        $this->db->query('ALTER TABLE `sales` MODIFY COLUMN `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->db->query('ALTER TABLE `sales` MODIFY COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP');

        // Set AUTO_INCREMENT if needed (auto_increment is handled by Forge, but mimic SQL original intent if needed)
        // $this->db->query('ALTER TABLE `sales` AUTO_INCREMENT = 25;'); // Optionally set initial AUTO_INCREMENT
    }

    public function down()
    {
        $this->forge->dropTable('sales', true);
    }
}
