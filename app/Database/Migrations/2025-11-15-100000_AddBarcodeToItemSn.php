<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-15
 * Github: github.com/mikhaelfelian
 * Description: Migration to alter item_sn table by adding barcode column for barcode tracking.
 * This file represents the Migration for AddBarcodeToItemSn.
 */
class AddBarcodeToItemSn extends Migration
{
    public function up()
    {
        // Add barcode column after sn column
        $fields = [
            'barcode' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'after'      => 'sn',
                'comment'    => 'Barcode for the serial number'
            ],
        ];
        $this->forge->addColumn('item_sn', $fields);

        // Add index for barcode for faster searches
        $this->forge->addKey('barcode', false, false, 'idx_barcode');
    }

    public function down()
    {
        // Remove index
        $this->forge->dropKey('item_sn', 'idx_barcode');

        // Remove column
        $this->forge->dropColumn('item_sn', 'barcode');
    }
}

