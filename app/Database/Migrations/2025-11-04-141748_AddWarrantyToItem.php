<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-04
 * Github: github.com/mikhaelfelian
 * Description: Migration to add warranty column to item table
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddWarrantyToItem extends Migration
{
    public function up()
    {
        // Check if column already exists
        if ($this->db->fieldExists('warranty', 'item')) {
            return;
        }

        $fields = [
            'warranty' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Warranty period in months'
            ]
        ];

        $this->forge->addColumn('item', $fields);
    }

    public function down()
    {
        if ($this->db->fieldExists('warranty', 'item')) {
            $this->forge->dropColumn('item', 'warranty');
        }
    }
}
