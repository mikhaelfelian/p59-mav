<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-09
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating sales_payments table to store payment details for each sale transaction.
 * This table links sales with payment platforms and methods.
 */
class CreateSalesPaymentsTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if ($this->db->tableExists('sales_payments')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sale_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Foreign key to sales.id'
            ],
            'platform_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Foreign key to platform.id (optional)'
            ],
            'method' => [
                'type'       => 'ENUM',
                'constraint' => ['cash', 'transfer', 'qris', 'credit', 'other'],
                'null'       => false,
                'default'    => 'cash',
                'comment'    => 'Payment method used'
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'null'       => false,
                'default'    => 0.00,
                'comment'    => 'Payment amount'
            ],
            'note' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Additional notes (e.g. cashier, transfer info, manual note)'
            ],
            'response' => [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Raw gateway response or JSON response from payment gateway'
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
            ],
        ]);

        // Primary key
        $this->forge->addKey('id', true);

        // Indexes for better query performance
        $this->forge->addKey('sale_id', false, false, 'idx_sale_id');
        $this->forge->addKey('platform_id', false, false, 'idx_platform_id');
        $this->forge->addKey(['sale_id', 'platform_id'], false, false, 'idx_sale_platform');

        // Create table
        $this->forge->createTable('sales_payments', false, [
            'ENGINE' => 'InnoDB',
            'COMMENT' => 'Stores payment details for each sale transaction. Links sales with payment platforms and methods.'
        ]);

        // Set DEFAULT CURRENT_TIMESTAMP for created_at
        $this->db->query("ALTER TABLE `sales_payments` MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Set DEFAULT NULL and ON UPDATE CURRENT_TIMESTAMP for updated_at
        $this->db->query("ALTER TABLE `sales_payments` MODIFY `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");

        // Add foreign key constraint to sales table (if sales table exists)
        if ($this->db->tableExists('sales')) {
            try {
                $this->db->query("ALTER TABLE `sales_payments` 
                    ADD CONSTRAINT `fk_sales_payments_sale_id` 
                    FOREIGN KEY (`sale_id`) 
                    REFERENCES `sales` (`id`) 
                    ON DELETE CASCADE 
                    ON UPDATE CASCADE");
            } catch (\Exception $e) {
                // Foreign key might already exist or table structure might not support it
                log_message('info', 'SalesPayments migration: Could not add foreign key constraint: ' . $e->getMessage());
            }
        }

        // Add foreign key constraint to platform table (if platform table exists)
        if ($this->db->tableExists('platform')) {
            try {
                $this->db->query("ALTER TABLE `sales_payments` 
                    ADD CONSTRAINT `fk_sales_payments_platform_id` 
                    FOREIGN KEY (`platform_id`) 
                    REFERENCES `platform` (`id`) 
                    ON DELETE SET NULL 
                    ON UPDATE CASCADE");
            } catch (\Exception $e) {
                // Foreign key might already exist or table structure might not support it
                log_message('info', 'SalesPayments migration: Could not add platform foreign key constraint: ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
        // Drop foreign keys first
        try {
            $this->db->query("ALTER TABLE `sales_payments` DROP FOREIGN KEY `fk_sales_payments_sale_id`");
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }

        try {
            $this->db->query("ALTER TABLE `sales_payments` DROP FOREIGN KEY `fk_sales_payments_platform_id`");
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }

        // Drop table
        $this->forge->dropTable('sales_payments', true);
    }
}

