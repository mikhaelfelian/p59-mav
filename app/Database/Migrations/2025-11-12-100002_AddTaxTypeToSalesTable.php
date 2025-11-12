<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-12
 * Github: github.com/mikhaelfelian
 * Description: Migration for adding tax_type field to sales table
 */
class AddTaxTypeToSalesTable extends Migration
{
    public function up()
    {
        // Check if column already exists
        if ($this->db->fieldExists('tax_type', 'sales')) {
            return;
        }

        $fields = [
            'tax_type' => [
                'type'       => 'ENUM',
                'constraint' => ['0', '1', '2'],
                'null'       => false,
                'default'    => '0',
                'after'      => 'tax_amount',
                'comment'    => '0=no tax, 1=include tax (PPN termasuk), 2=added tax (PPN ditambahkan)'
            ],
        ];

        $this->forge->addColumn('sales', $fields);
    }

    public function down()
    {
        if ($this->db->fieldExists('tax_type', 'sales')) {
            $this->forge->dropColumn('sales', 'tax_type');
        }
    }
}
