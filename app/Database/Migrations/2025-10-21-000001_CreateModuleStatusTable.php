<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateModuleStatusTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id_module_status' => [
                'type' => 'TINYINT',
                'auto_increment' => true,
            ],
            'nama_status' => ['type' => 'VARCHAR','constraint' => 50,'null' => true],
            'keterangan' => ['type' => 'VARCHAR','constraint' => 255,'null' => true],
        ]);
$this->forge->addKey('id_module_status', true);

        $this->forge->createTable('module_status', true);
    }

    public function down()
    {
        $this->forge->dropTable('module_status', true);
    }
}
