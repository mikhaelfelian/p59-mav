<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAgentPriceToItem extends Migration
{
    public function up()
    {
        $fields = [
            'agent_price' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'null'       => false,
                'default'    => 0.00,
                'after'      => 'price',
            ],
        ];

        $this->forge->addColumn('item', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('item', 'agent_price');
    }
}


