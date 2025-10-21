<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateModuleTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id_module' => ['type'=>'SMALLINT','unsigned'=>true,'auto_increment'=>true],
            'nama_module' => ['type'=>'VARCHAR','constraint'=>50,'null'=>true],
            'judul_module' => ['type'=>'VARCHAR','constraint'=>50,'null'=>true],
            'id_module_status' => ['type'=>'TINYINT','null'=>true],
            'login' => ['type'=>'ENUM','constraint'=>['Y','N','R'],'default'=>'Y'],
            'deskripsi' => ['type'=>'VARCHAR','constraint'=>50,'null'=>true],
        ]);
$this->forge->addKey('id_module', true);
        $this->forge->addUniqueKey('nama_module');
$this->forge->addForeignKey('id_module_status', 'module_status', 'id_module_status', 'SET NULL', 'CASCADE');
        $this->forge->createTable('module', true);
    }

    public function down()
    {
        $this->forge->dropTable('module', true);
    }
}
