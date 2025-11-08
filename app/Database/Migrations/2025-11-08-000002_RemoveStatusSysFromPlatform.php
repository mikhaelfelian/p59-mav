<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-08
 * Github: github.com/mikhaelfelian
 * Description: Migration to remove status_sys column from platform table
 * Only using status field now
 */
class RemoveStatusSysFromPlatform extends Migration
{
    public function up()
    {
        // Check if column exists before dropping
        if ($this->db->fieldExists('status_sys', 'platform')) {
            $this->forge->dropColumn('platform', 'status_sys');
        }
    }

    public function down()
    {
        // Re-add the column if rollback is needed
        $fields = [
            'status_sys' => [
                'type'       => 'ENUM',
                'constraint' => ['0', '1'],
                'null'       => false,
                'default'    => '0',
                'after'      => 'status',
            ]
        ];
        $this->forge->addColumn('platform', $fields);
    }
}

