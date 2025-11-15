<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-15
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating sales_gateway_logs table to track all invoice numbers sent to payment gateway.
 * This prevents reusing invoice numbers that have already been sent to the gateway (payment gateway rule).
 */
class CreateSalesGatewayLogsTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if ($this->db->tableExists('sales_gateway_logs')) {
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
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Invoice number (orderId) sent to payment gateway - cannot be reused'
            ],
            'order_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Same as invoice_no, for clarity and compatibility'
            ],
            'platform_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Foreign key to platform.id (which payment platform was used)'
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '20,2',
                'null'       => false,
                'default'    => 0.00,
                'comment'    => 'Amount sent to payment gateway'
            ],
            'payload' => [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Full JSON payload sent to payment gateway (for debugging)'
            ],
            'response' => [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Full JSON response from payment gateway'
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Status from gateway (PENDING, PAID, FAILED, etc.)'
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

        // Unique index on invoice_no to prevent duplicates (enforces gateway rule)
        $this->forge->addUniqueKey('invoice_no', 'idx_unique_invoice_no');

        // Indexes for better query performance
        $this->forge->addKey('order_id', false, false, 'idx_order_id');
        $this->forge->addKey('platform_id', false, false, 'idx_platform_id');
        $this->forge->addKey('status', false, false, 'idx_status');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        // Create table
        $this->forge->createTable('sales_gateway_logs', false, [
            'ENGINE' => 'InnoDB',
            'COMMENT' => 'Tracks all invoice numbers sent to payment gateway. Once an invoice number is sent, it cannot be reused (payment gateway rule).'
        ]);

        // Set DEFAULT CURRENT_TIMESTAMP for created_at
        $this->db->query("ALTER TABLE `sales_gateway_logs` MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Set DEFAULT NULL and ON UPDATE CURRENT_TIMESTAMP for updated_at
        $this->db->query("ALTER TABLE `sales_gateway_logs` MODIFY `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");

        // Add foreign key constraint to platform table (if exists)
        if ($this->db->tableExists('platform')) {
            $this->db->query("
                ALTER TABLE `sales_gateway_logs`
                ADD CONSTRAINT `fk_sales_gateway_logs_platform_id`
                FOREIGN KEY (`platform_id`) 
                REFERENCES `platform` (`id`) 
                ON DELETE SET NULL 
                ON UPDATE CASCADE
            ");
        }
    }

    public function down()
    {
        // Drop foreign keys first
        if ($this->db->tableExists('sales_gateway_logs')) {
            // Drop foreign key constraints
            $this->db->query("ALTER TABLE `sales_gateway_logs` DROP FOREIGN KEY IF EXISTS `fk_sales_gateway_logs_platform_id`");
            
            // Drop table
            $this->forge->dropTable('sales_gateway_logs', true);
        }
    }
}

