<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateModulePermissionTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id_module_permission' => ['type'=>'SMALLINT','unsigned'=>true,'auto_increment'=>true],
            'id_module' => ['type'=>'SMALLINT','unsigned'=>true,'default'=>0],
            'nama_permission' => ['type'=>'VARCHAR','constraint'=>50,'null'=>false],
            'judul_permission' => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'keterangan' => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
        ]);
$this->forge->addKey('id_module_permission', true);
        $this->forge->addUniqueKey(['id_module','nama_permission']);
$this->forge->addForeignKey('id_module', 'module', 'id_module', 'CASCADE', 'CASCADE');
        $this->forge->createTable('module_permission', true);
    }

    public function down()
    {
        $this->forge->dropTable('module_permission', true);
    }
}
