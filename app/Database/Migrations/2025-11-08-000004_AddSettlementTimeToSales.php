<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-08
 * Github: github.com/mikhaelfelian
 * Description: Migration to add settlement_time column to sales table
 * This column stores the payment settlement time from payment gateway callback
 */
class AddSettlementTimeToSales extends Migration
{
    public function up()
    {
        // Check if column already exists
        if ($this->db->fieldExists('settlement_time', 'sales')) {
            return;
        }
        
        $fields = [
            'settlement_time' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'payment_status',
                'comment' => 'Payment settlement time from gateway callback'
            ]
        ];
        
        $this->forge->addColumn('sales', $fields);
    }

    public function down()
    {
        // Drop settlement_time column if exists
        if ($this->db->fieldExists('settlement_time', 'sales')) {
            $this->forge->dropColumn('sales', 'settlement_time');
        }
    }
}

