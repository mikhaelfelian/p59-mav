<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-15
 * Github: github.com/mikhaelfelian
 * Description: Migration to add admin_note column to sales table
 * This column stores courier/AWB information for admin use only.
 */
class AddAdminNoteToSales extends Migration
{
    public function up()
    {
        // Check if column already exists
        if ($this->db->fieldExists('admin_note', 'sales')) {
            return;
        }

        $fields = [
            'admin_note' => [
                'type'    => 'TEXT',
                'null'    => true,
                'default' => null,
                'comment' => 'Catatan admin untuk kurir, AWB, dll (hanya untuk admin)',
                'after'   => 'note'
            ],
        ];

        $this->forge->addColumn('sales', $fields);
    }

    public function down()
    {
        if ($this->db->fieldExists('admin_note', 'sales')) {
            $this->forge->dropColumn('sales', 'admin_note');
        }
    }
}
