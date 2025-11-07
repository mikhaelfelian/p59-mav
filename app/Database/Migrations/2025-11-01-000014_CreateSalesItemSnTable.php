<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating sales_item_sn table to map multiple serial numbers (SN) to one sales item.
 * This file represents the Migration for CreateSalesItemSnTable.
 */
class CreateSalesItemSnTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('sales_item_sn')) {
            return;
        }
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sales_item_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'item_sn_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'sn' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
            ],
        ]);

        // Primary key
        $this->forge->addKey('id', true);

        // Indexes
        $this->forge->addKey(['sales_item_id', 'item_sn_id'], false, false, 'idx_sales_item_sn');

        // Create table with comment
        $this->forge->createTable('sales_item_sn', false, [
            'ENGINE' => 'InnoDB',
            'COMMENT' => 'Maps multiple serial numbers (SN) to one sales item.'
        ]);

        // Set DEFAULT CURRENT_TIMESTAMP for created_at
        $this->db->query("ALTER TABLE `sales_item_sn` MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('sales_item_sn', true);
    }
}

