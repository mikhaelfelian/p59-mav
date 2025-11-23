<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-21
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating item_sn_replaced table to track serial number replacement history
 * This table records each replacement event, allowing tracking of how many times a serial number has been replaced.
 * This file represents the Migration for CreateItemSnReplacedTable.
 */
class CreateItemSnReplacedTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if (in_array('item_sn_replaced', $this->db->listTables())) {
            return;
        }

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
                'comment'    => 'FK to item.id',
            ],
            'sn_old' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Old serial number that was replaced',
            ],
            'sn_replaced' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'New serial number that replaced the old one',
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true); // Primary key
        $this->forge->addKey('item_id');
        $this->forge->addKey('sn_old');
        $this->forge->addKey('sn_replaced');

        // Create table
        $this->forge->createTable('item_sn_replaced', false, [
            'ENGINE'    => 'InnoDB',
            'CHARSET'   => 'utf8mb4',
            'COLLATE'   => 'utf8mb4_general_ci',
            'COMMENT'   => 'Tracks serial number replacement history. Records each replacement event to allow counting how many times a SN has been replaced.',
        ]);

        // Set DEFAULT CURRENT_TIMESTAMP for created_at
        $this->db->query("ALTER TABLE `item_sn_replaced` MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('item_sn_replaced', true);
    }
}

