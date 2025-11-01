<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating sales_payments table to handle multiple payment entries per sale 
 * (cash, transfer, QRIS, gateway).
 * This file represents the Migration for CreateSalesPaymentsTable.
 */
class CreateSalesPaymentsTable extends Migration
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
            'sale_id' => [
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
            'payment_date' => [
                'type'    => 'DATETIME',
                'null'    => false,
            ],
            'payment_method' => [
                'type'       => 'ENUM',
                'constraint' => ['cash', 'transfer', 'qris', 'gateway'],
                'null'       => false,
                'default'    => 'cash',
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
                'default'    => 0.00,
            ],
            'payment_ref' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'payment_gateway' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['waiting', 'paid', 'failed', 'refunded'],
                'null'       => false,
                'default'    => 'waiting',
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
        $this->forge->addKey('sale_id', false, false, 'idx_sale_payment');
        $this->forge->addKey('status', false, false, 'idx_payment_status');

        // Create table with comment
        $this->forge->createTable('sales_payments', false, [
            'ENGINE' => 'InnoDB',
            'COMMENT' => 'Handles multiple payment entries per sale (cash, transfer, QRIS, gateway).'
        ]);

        // Set DEFAULT CURRENT_TIMESTAMP for created_at
        $this->db->query("ALTER TABLE `sales_payments` MODIFY `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Set DEFAULT CURRENT_TIMESTAMP for payment_date
        $this->db->query("ALTER TABLE `sales_payments` MODIFY `payment_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Set DEFAULT NULL and ON UPDATE CURRENT_TIMESTAMP for updated_at
        $this->db->query("ALTER TABLE `sales_payments` MODIFY `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('sales_payments', true);
    }
}

