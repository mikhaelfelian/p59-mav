<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-11
 * Github: github.com/mikhaelfelian
 * Description: Migration to add missing columns to sales_item_sn table (no_hp, plat_code, plat_number, plat_last, file, activated_at, expired_at, updated_at)
 */
class AddFieldsToSalesItemSn extends Migration
{
    public function up()
    {
        // Check if columns already exist
        if ($this->db->fieldExists('no_hp', 'sales_item_sn')) {
            return;
        }

        $fields = [
            'no_hp' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => null,
                'after'      => 'sn',
                'comment'    => 'Phone number'
            ],
            'plat_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'after'      => 'no_hp',
                'comment'    => 'Vehicle plate code (e.g., B, H, D)'
            ],
            'plat_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'after'      => 'plat_code',
                'comment'    => 'Vehicle plate number (e.g., 4575)'
            ],
            'plat_last' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'default'    => null,
                'after'      => 'plat_number',
                'comment'    => 'Vehicle plate last code (e.g., PBP, ABC)'
            ],
            'file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'plat_last',
                'comment'    => 'File path or filename'
            ],
            'activated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
                'after'   => 'file',
                'comment' => 'Activation timestamp'
            ],
            'expired_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
                'after'   => 'activated_at',
                'comment' => 'Expiration timestamp'
            ],
            'updated_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
                'after'   => 'expired_at',
                'comment' => 'Last update timestamp'
            ],
        ];

        $this->forge->addColumn('sales_item_sn', $fields);
    }

    public function down()
    {
        if ($this->db->fieldExists('no_hp', 'sales_item_sn')) {
            $this->forge->dropColumn('sales_item_sn', [
                'no_hp',
                'plat_code',
                'plat_number',
                'plat_last',
                'file',
                'activated_at',
                'expired_at',
                'updated_at'
            ]);
        }
    }
}

