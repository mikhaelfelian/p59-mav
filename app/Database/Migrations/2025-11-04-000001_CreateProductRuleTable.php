<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-04
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating item_rule table to store product rules (cashback or buy_get)
 * This file represents the Migration for CreateProductRuleTable.
 */
class CreateProductRuleTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('item_rule')) {
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
            ],
            'rule_type' => [
                'type'       => 'ENUM',
                'constraint' => ['cashback', 'buy_get'],
                'null'       => false,
            ],
            'threshold_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'null'       => true,
                'default'    => null,
            ],
            'cashback_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '16,2',
                'null'       => true,
                'default'    => null,
            ],
            'min_qty' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
            ],
            'bonus_item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'bonus_qty' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
            ],
            'is_multiple' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
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

        // Primary key
        $this->forge->addKey('id', true);

        // Indexes
        $this->forge->addKey('item_id');
        $this->forge->addKey('rule_type');
        $this->forge->addKey('is_active');

        // Foreign key constraint
        $this->forge->addForeignKey('item_id', 'item', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('bonus_item_id', 'item', 'id', 'SET NULL', 'CASCADE');

        // Create table
        $this->forge->createTable('item_rule', true);
    }

    public function down()
    {
        $this->forge->dropTable('item_rule', true);
    }
}

