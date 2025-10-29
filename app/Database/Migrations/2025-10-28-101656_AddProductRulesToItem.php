<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-10-28
 * Github: github.com/mikhaelfelian
 * description: Migration to add product_rules column to item table
 * This file represents the Migration for AddProductRulesToItem.
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProductRulesToItem extends Migration
{
    public function up()
    {
        $this->forge->addColumn('item', [
            'product_rules' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'status'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('item', 'product_rules');
    }
}
