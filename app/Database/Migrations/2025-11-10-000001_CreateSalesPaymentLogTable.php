<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-10
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating sales_payment_log table to store payment gateway callback logs.
 * This table logs all payment gateway callbacks and responses for audit purposes.
 */
class CreateSalesPaymentLogTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if ($this->db->tableExists('sales_payment_log')) {
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
                'null'       => true,
                'default'    => null,
                'comment'    => 'Foreign key to sales.id (nullable for error cases)'
            ],
            'platform_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Foreign key to platform.id (optional)'
            ],
            'response' => [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Raw gateway response or JSON response from payment gateway callback'
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
        $this->forge->addKey('created_at', false, false, 'idx_created_at');

        // Create table
        $this->forge->createTable('sales_payment_log', false, [
            'ENGINE' => 'InnoDB',
            'COMMENT' => 'Stores payment gateway callback logs for audit and tracking purposes.'
        ]);

        // Set DEFAULT CURRENT_TIMESTAMP for created_at
        $this->db->query("ALTER TABLE `sales_payment_log` MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Set DEFAULT NULL and ON UPDATE CURRENT_TIMESTAMP for updated_at
        $this->db->query("ALTER TABLE `sales_payment_log` MODIFY `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");

        // Add foreign key constraint to sales table
        // Check if sales table exists before adding foreign key
        // Note: sale_id can be NULL for error cases, so FK only applies when sale_id is not NULL
        if ($this->db->tableExists('sales')) {
            $this->db->query("
                ALTER TABLE `sales_payment_log`
                ADD CONSTRAINT `fk_sales_payment_log_sale_id`
                FOREIGN KEY (`sale_id`) 
                REFERENCES `sales` (`id`) 
                ON DELETE CASCADE 
                ON UPDATE CASCADE
            ");
        }

        // Add foreign key constraint to platform table (if exists)
        if ($this->db->tableExists('platform')) {
            $this->db->query("
                ALTER TABLE `sales_payment_log`
                ADD CONSTRAINT `fk_sales_payment_log_platform_id`
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
        if ($this->db->tableExists('sales_payment_log')) {
            // Drop foreign key constraints
            $this->db->query("ALTER TABLE `sales_payment_log` DROP FOREIGN KEY IF EXISTS `fk_sales_payment_log_sale_id`");
            $this->db->query("ALTER TABLE `sales_payment_log` DROP FOREIGN KEY IF EXISTS `fk_sales_payment_log_platform_id`");
            
            // Drop table
            $this->forge->dropTable('sales_payment_log', true);
        }
    }
}

