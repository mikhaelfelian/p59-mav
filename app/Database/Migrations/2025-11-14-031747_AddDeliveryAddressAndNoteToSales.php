<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-14
 * Github: github.com/mikhaelfelian
 * Description: Migration for adding delivery_address and note fields to sales table
 */
class AddDeliveryAddressAndNoteToSales extends Migration
{
    public function up()
    {
        $fields = [];
        
        // Add delivery_address field if it doesn't exist
        if (!$this->db->fieldExists('delivery_address', 'sales')) {
            $fields['delivery_address'] = [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'grand_total',
                'comment'    => 'Delivery address for the order (can be agent registered address or custom address)'
            ];
        }
        
        // Add note field if it doesn't exist
        if (!$this->db->fieldExists('note', 'sales')) {
            $fields['note'] = [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'delivery_address',
                'comment'    => 'Order notes/comments'
            ];
        }
        
        if (!empty($fields)) {
            $this->forge->addColumn('sales', $fields);
        }
    }

    public function down()
    {
        // Drop delivery_address field if it exists
        if ($this->db->fieldExists('delivery_address', 'sales')) {
            $this->forge->dropColumn('sales', 'delivery_address');
        }
        
        // Drop note field if it exists
        if ($this->db->fieldExists('note', 'sales')) {
            $this->forge->dropColumn('sales', 'note');
        }
    }
}
