<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-08
 * Github: github.com/mikhaelfelian
 * Description: Migration to add gateway payment fields to platform table
 * Adds: gw_code, gw_status, and logo fields for payment gateway integration
 */
class AddGatewayFieldsToPlatform extends Migration
{
    public function up()
    {
        $fields = [
            'gw_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'default'    => null,
                'after'      => 'status',
                'comment'    => 'Gateway code (e.g., midtrans, stripe)'
            ],
            'gw_status' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 0,
                'after'      => 'gw_code',
                'comment'    => 'Gateway status: 1=active, 0=inactive'
            ],
            'logo' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'gw_status',
                'comment'    => 'Logo file path for gateway'
            ]
        ];

        $this->forge->addColumn('platform', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('platform', ['gw_code', 'gw_status', 'logo']);
    }
}

