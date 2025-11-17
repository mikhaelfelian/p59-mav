<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsReceiveToSales extends Migration
{
    public function up()
    {
        $fields = [
            'is_receive' => [
                'type'       => 'ENUM',
                'constraint' => ['0', '1'],
                'default'    => '0',
                'after'      => 'payment_status',
            ],
        ];

        $this->forge->addColumn('sales', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('sales', 'is_receive');
    }
}


