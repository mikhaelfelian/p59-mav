<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSalesDetailTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if ($this->db->tableExists('sales_detail')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'sale_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Foreign key to sales table'
            ],
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'Foreign key to item table'
            ],
            'variant_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Foreign key to item_variant table (optional)'
            ],
            'sn' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Serial number'
            ],
            'item' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Item name or reference'
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'default'    => 0.00,
                'comment'    => 'Item price per unit'
            ],
            'qty' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 1,
                'comment'    => 'Quantity'
            ],
            'disc' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'default'    => 0.00,
                'comment'    => 'Discount amount'
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => false,
                'default'    => 0.00,
                'comment'    => 'Total amount (price * qty - disc)'
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('sale_id');
        $this->forge->addKey('item_id');
        $this->forge->addKey('variant_id');
        $this->forge->addKey('sn');

        // Add foreign key constraints
        $this->forge->addForeignKey('sale_id', 'sales', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'item', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('variant_id', 'item_variant', 'id', 'RESTRICT', 'RESTRICT');

        $this->forge->createTable('sales_detail', true);
    }

    public function down()
    {
        if ($this->db->tableExists('sales_detail')) {
            $this->forge->dropTable('sales_detail', true);
        }
    }
}

