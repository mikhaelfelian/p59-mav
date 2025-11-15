<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-01-20
 * Github: github.com/mikhaelfelian
 * Description: Migration to add is_receive column to sales_item_sn table
 * This column tracks whether the agent has received the serial number.
 */
class AddIsReceiveToSalesItemSn extends Migration
{
    public function up()
    {
        // Check if column already exists
        if ($this->db->fieldExists('is_receive', 'sales_item_sn')) {
            return;
        }

        $fields = [
            'is_receive' => [
                'type'       => 'ENUM',
                'constraint' => ['0', '1'],
                'default'    => '0',
                'null'       => false,
                'comment'    => '0 = not received, 1 = received by agent',
                'after'      => 'expired_at'
            ],
            'receive_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => true,
                'default' => null,
                'comment' => 'Timestamp when SN was received by agent',
                'after'   => 'is_receive'
            ],
        ];

        $this->forge->addColumn('sales_item_sn', $fields);
    }

    public function down()
    {
        if ($this->db->fieldExists('receive_at', 'sales_item_sn')) {
            $this->forge->dropColumn('sales_item_sn', 'receive_at');
        }
        if ($this->db->fieldExists('is_receive', 'sales_item_sn')) {
            $this->forge->dropColumn('sales_item_sn', 'is_receive');
        }
    }
}

