<?php

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-04
 * Github: github.com/mikhaelfelian
 * Description: Migration to add plate fields to customer table
 */

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPlateFieldsToCustomer extends Migration
{
    public function up()
    {
        // Check if columns already exist
        if ($this->db->fieldExists('plate_code', 'customer')) {
            return;
        }

        $fields = [
            'plate_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Vehicle plate code (e.g., H, B, D)'
            ],
            'plate_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Vehicle plate number (e.g., 4575)'
            ],
            'plate_suffix' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'comment'    => 'Vehicle plate suffix (e.g., PBP, ABC)'
            ]
        ];

        $this->forge->addColumn('customer', $fields);
        
        // Add index for plate search
        $this->forge->addKey(['plate_code', 'plate_number', 'plate_suffix'], false, false, 'idx_plate');
    }

    public function down()
    {
        if ($this->db->fieldExists('plate_code', 'customer')) {
            $this->forge->dropColumn('customer', ['plate_code', 'plate_number', 'plate_suffix']);
        }
    }
}
