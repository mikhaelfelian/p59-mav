<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Migration to alter item_sn table by adding variant_id column to link serial numbers to product variants.
 * This file represents the Migration for AlterItemSnAddVariantId.
 */
class AlterItemSnAddVariantId extends Migration
{
    public function up()
    {
        // Add variant_id column after item_id
        $fields = [
            'variant_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'item_id',
            ],
        ];
        $this->forge->addColumn('item_sn', $fields);

        // Add index for variant_id
        $this->forge->addKey('variant_id', false, false, 'idx_variant');

        // Update table comment
        $this->db->query("ALTER TABLE `item_sn` COMMENT = 'Stores serial numbers (SN) of items for tracking activation, expiration, replacement, and sales per agent. Adds a reference to item_variant.id to link each serial number to a specific variant when applicable.'");
    }

    public function down()
    {
        // Remove index
        $this->forge->dropKey('item_sn', 'idx_variant');

        // Remove column
        $this->forge->dropColumn('item_sn', 'variant_id');
    }
}

