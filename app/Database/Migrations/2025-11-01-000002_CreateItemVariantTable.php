<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating item_variant table to store product variants under the same item.
 * Each variant can have a unique SKU, price, and stock level, allowing the POS system to manage 
 * product options (e.g. size, color, or packaging type) separately.
 * This file represents the Migration for CreateItemVariantTable.
 */
class CreateItemVariantTable extends Migration
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
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'variant_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'sku_variant' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'stock' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
                'default'    => 0,
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => false,
                'default'    => 0.00,
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

        // Indexes
        $this->forge->addKey(['item_id', 'variant_name'], false, false, 'idx_item_variant');

        // Create table with comment
        $this->forge->createTable('item_variant', false, [
            'ENGINE' => 'InnoDB',
            'COMMENT' => 'This table stores product variants under the same item. Each variant can have a unique SKU, price, and stock level, allowing the POS system to manage product options (e.g. size, color, or packaging type) separately.'
        ]);

        // Add UNIQUE constraint on sku_variant field
        $this->db->query("ALTER TABLE `item_variant` ADD UNIQUE KEY `unique_sku_variant` (`sku_variant`)");

        // Set DEFAULT CURRENT_TIMESTAMP for created_at
        $this->db->query("ALTER TABLE `item_variant` MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Set DEFAULT NULL and ON UPDATE CURRENT_TIMESTAMP for updated_at
        $this->db->query("ALTER TABLE `item_variant` MODIFY `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('item_variant', true);
    }
}

