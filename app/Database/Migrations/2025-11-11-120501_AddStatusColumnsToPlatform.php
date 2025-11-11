<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusColumnsToPlatform extends Migration
{
    public function up()
    {
        $fields = [];

        if (!$this->db->fieldExists('status_kredit', 'platform')) {
            $fields['status_kredit'] = [
                'type'       => 'ENUM',
                'constraint' => ['0', '1'],
                'default'    => '0',
                'null'       => false,
                'after'      => 'status',
            ];
        }

        if (!$this->db->fieldExists('status_sys', 'platform')) {
            $fields['status_sys'] = [
                'type'       => 'ENUM',
                'constraint' => ['0', '1'],
                'default'    => '0',
                'null'       => false,
                'after'      => 'status_kredit',
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('platform', $fields);
        }
    }

    public function down()
    {
        $drop = [];

        if ($this->db->fieldExists('status_kredit', 'platform')) {
            $drop[] = 'status_kredit';
        }

        if ($this->db->fieldExists('status_sys', 'platform')) {
            $drop[] = 'status_sys';
        }

        if (!empty($drop)) {
            $this->forge->dropColumn('platform', $drop);
        }
    }
}


