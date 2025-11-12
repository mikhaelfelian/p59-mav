<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-12
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating sales_fee table to store fees for each sale transaction
 */
class CreateSalesFeeTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('sales_fee')) {
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
            'fee_type_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Foreign key to fee_type.id'
            ],
            'fee_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Custom name override if needed'
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'null'       => false,
                'default'    => 0.00,
                'comment'    => 'Fee amount'
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
        $this->forge->addKey('fee_type_id', false, false, 'idx_fee_type_id');
        $this->forge->addKey(['sale_id', 'fee_type_id'], false, false, 'idx_sale_fee_type');

        // Create table
        $this->forge->createTable('sales_fee', false, [
            'ENGINE' => 'InnoDB',
            'COMMENT' => 'Stores fees for each sale transaction (shipment, insurance, handling, etc.)'
        ]);

        // Set DEFAULT CURRENT_TIMESTAMP for created_at
        $this->db->query("ALTER TABLE `sales_fee` MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Set DEFAULT NULL and ON UPDATE CURRENT_TIMESTAMP for updated_at
        $this->db->query("ALTER TABLE `sales_fee` MODIFY `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");

        // Add foreign key constraint to sales table
        if ($this->db->tableExists('sales')) {
            try {
                $this->db->query("ALTER TABLE `sales_fee` 
                    ADD CONSTRAINT `fk_sales_fee_sale_id` 
                    FOREIGN KEY (`sale_id`) 
                    REFERENCES `sales` (`id`) 
                    ON DELETE CASCADE 
                    ON UPDATE CASCADE");
            } catch (\Exception $e) {
                log_message('info', 'SalesFee migration: Could not add sale_id foreign key constraint: ' . $e->getMessage());
            }
        }

        // Add foreign key constraint to fee_type table
        if ($this->db->tableExists('fee_type')) {
            try {
                $this->db->query("ALTER TABLE `sales_fee` 
                    ADD CONSTRAINT `fk_sales_fee_fee_type_id` 
                    FOREIGN KEY (`fee_type_id`) 
                    REFERENCES `fee_type` (`id`) 
                    ON DELETE RESTRICT 
                    ON UPDATE CASCADE");
            } catch (\Exception $e) {
                log_message('info', 'SalesFee migration: Could not add fee_type_id foreign key constraint: ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
        // Drop foreign keys first
        try {
            $this->db->query("ALTER TABLE `sales_fee` DROP FOREIGN KEY `fk_sales_fee_sale_id`");
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }

        try {
            $this->db->query("ALTER TABLE `sales_fee` DROP FOREIGN KEY `fk_sales_fee_fee_type_id`");
        } catch (\Exception $e) {
            // Ignore if doesn't exist
        }

        // Drop table
        $this->forge->dropTable('sales_fee', true);
    }
}
