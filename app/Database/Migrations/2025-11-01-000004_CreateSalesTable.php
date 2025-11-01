<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating sales table to store main sales transactions, linking customers, agents, users, and totals.
 * Integrates with products, variants, and SN tracking.
 * This file represents the Migration for CreateSalesTable.
 */
class CreateSalesTable extends Migration
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
            'customer_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'agent_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'warehouse_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'invoice_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'invoice_date' => [
                'type'       => 'DATE',
                'null'       => false,
            ],
            'total_qty' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
                'default'    => 0.00,
            ],
            'subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
                'default'    => 0.00,
            ],
            'discount_type' => [
                'type'       => 'ENUM',
                'constraint' => ['%', 'rp'],
                'null'       => true,
                'default'    => null,
            ],
            'discount_value' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
                'default'    => 0.00,
            ],
            'discount_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
                'default'    => 0.00,
            ],
            'tax_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'null'       => false,
                'default'    => 0.00,
            ],
            'tax_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
                'default'    => 0.00,
            ],
            'grand_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
                'default'    => 0.00,
            ],
            'total_payment' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
                'default'    => 0.00,
            ],
            'balance_due' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
                'default'    => 0.00,
            ],
            'status_payment' => [
                'type'       => 'ENUM',
                'constraint' => ['unpaid', 'partial', 'paid', 'refunded'],
                'null'       => false,
                'default'    => 'unpaid',
            ],
            'status_order' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'completed', 'cancelled'],
                'null'       => false,
                'default'    => 'pending',
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => false,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        // Primary key
        $this->forge->addKey('id', true);

        // Indexes
        $this->forge->addKey('invoice_code', false, false, 'idx_invoice_code');
        $this->forge->addKey('customer_id', false, false, 'idx_customer');
        $this->forge->addKey('agent_id', false, false, 'idx_agent');

        // Create table with comment
        $this->forge->createTable('sales', false, [
            'ENGINE' => 'InnoDB',
            'COMMENT' => 'Stores main sales transactions, linking customers, agents, users, and totals. Integrates with products, variants, and SN tracking.'
        ]);

        // Add UNIQUE constraint on invoice_code field
        $this->db->query("ALTER TABLE `sales` ADD UNIQUE KEY `unique_invoice_code` (`invoice_code`)");

        // Set DEFAULT CURRENT_TIMESTAMP for created_at
        $this->db->query("ALTER TABLE `sales` MODIFY `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Set DEFAULT NULL and ON UPDATE CURRENT_TIMESTAMP for updated_at
        $this->db->query("ALTER TABLE `sales` MODIFY `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('sales', true);
    }
}

