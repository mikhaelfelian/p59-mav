<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-04
 * Github: github.com/mikhaelfelian
 * Description: Migration to add plat fields (plat_code, plat_number, plat_last) to customer table
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlatFieldsToCustomer extends Migration
{
    public function up()
    {
        // Check if columns already exist
        if ($this->db->fieldExists('plat_code', 'customer')) {
            return;
        }

        $fields = [
            'plat_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Vehicle plate code (e.g., B, H, D)'
            ],
            'plat_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Vehicle plate number (e.g., 4575)'
            ],
            'plat_last' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Vehicle plate last code (e.g., PBP, ABC)'
            ]
        ];

        $this->forge->addColumn('customer', $fields);
        
        // Add index for plate search
        $this->forge->addKey(['plat_code', 'plat_number', 'plat_last'], false, false, 'idx_plat');
    }

    public function down()
    {
        if ($this->db->fieldExists('plat_code', 'customer')) {
            $this->forge->dropColumn('customer', ['plat_code', 'plat_number', 'plat_last']);
        }
    }
}
