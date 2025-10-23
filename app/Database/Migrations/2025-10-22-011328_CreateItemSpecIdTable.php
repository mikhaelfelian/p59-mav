<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-22 - refer date today not past or before
 * Github: github.com/mikhaelfelian
 * description: This migration creates the item_spec_id table which stores the relationship between items and their specifications. 
 * Each record links an item to a specification, the user that added/owns that spec, and optionally stores a textual value for the item-spec relationship (such as a specific spec value for this item). Includes created_at and updated_at for auditability.
 * This file represents the Migration.
 */
class CreateItemSpecIdTable extends Migration
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
            'item_spec_id' => [
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
            'value' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'The specific value for this item-spec relation (e.g., 16GB, Red, etc.)',
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
        $this->forge->addKey('item_spec_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey(['item_id', 'item_spec_id'], false, false, 'unique_item_spec');
        
        // Table comment/description (only supported in some DBs, e.g., MySQL)
        $this->forge->createTable('item_spec_id', false, [
            'COMMENT' => 'Stores links between items and specifications, with optional value and user tracking.'
        ]);
        
        // Add foreign key constraints
        $this->forge->addForeignKey('item_id', 'item', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_spec_id', 'item_spec', 'id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropTable('item_spec_id');
    }
}