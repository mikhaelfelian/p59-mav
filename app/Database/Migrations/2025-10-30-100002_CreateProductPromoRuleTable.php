<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProductPromoRuleTable extends Migration
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
            'start_date' => [ 'type' => 'DATE', 'null' => true ],
            'end_date'   => [ 'type' => 'DATE', 'null' => true ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default'    => 'active',
            ],
            'notes' => [ 'type' => 'TEXT', 'null' => true ],
            'created_at' => [ 'type' => 'DATETIME', 'null' => true ],
            'updated_at' => [ 'type' => 'DATETIME', 'null' => true ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('item_id');
        $this->forge->addKey('status');
        
        // Create table with check for existing table
        if (!$this->db->tableExists('item_promo_rule')) {
            $this->forge->createTable('item_promo_rule', true);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('item_promo_rule')) {
            $this->forge->dropTable('item_promo_rule', true);
        }
    }
}


