<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: This migration creates the item_agent table which stores agent pricing for items. Each record links an item to a user (agent) with a specific price and active status.
 * This file represents the Migration.
 */
class CreateItemAgentTable extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
                'default'    => 0.00,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('item_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('is_active');
        $this->forge->addKey(['item_id', 'user_id'], false, false, 'unique_item_user');
        
        // Table comment/description
        $this->forge->createTable('item_agent', false, [
            'COMMENT' => 'Stores agent pricing for items with user tracking and active status.'
        ]);
        
        // Add foreign key constraint
        $this->forge->addForeignKey('item_id', 'item', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropTable('item_agent');
    }
}