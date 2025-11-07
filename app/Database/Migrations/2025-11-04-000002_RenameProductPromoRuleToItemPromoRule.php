<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration to rename product_promo_rule to item_promo_rule to match Item* naming convention
 */
class RenameProductPromoRuleToItemPromoRule extends Migration
{
    public function up()
    {
        // If product_promo_rule exists and item_promo_rule doesn't, rename it
        if ($this->db->tableExists('product_promo_rule') && !$this->db->tableExists('item_promo_rule')) {
            $this->db->query('RENAME TABLE `product_promo_rule` TO `item_promo_rule`');
        }
        
        // If product_promo_rule doesn't exist but item_promo_rule also doesn't exist, create it
        if (!$this->db->tableExists('product_promo_rule') && !$this->db->tableExists('item_promo_rule')) {
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
                'bonus_item_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'min_qty' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 1,
                ],
                'bonus_qty' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 1,
                ],
                'is_multiple' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'start_date' => ['type' => 'DATE', 'null' => true],
                'end_date'   => ['type' => 'DATE', 'null' => true],
                'status' => [
                    'type'       => 'ENUM',
                    'constraint' => ['active', 'inactive'],
                    'default'    => 'active',
                ],
                'notes' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('item_id');
            $this->forge->addKey('status');
            $this->forge->createTable('item_promo_rule', true);
        }
    }

    public function down()
    {
        // If item_promo_rule exists and product_promo_rule doesn't, rename it back
        if ($this->db->tableExists('item_promo_rule') && !$this->db->tableExists('product_promo_rule')) {
            $this->db->query('RENAME TABLE `item_promo_rule` TO `product_promo_rule`');
        }
    }
}

