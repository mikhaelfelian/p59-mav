<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoleTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id_role' => ['type'=>'SMALLINT','unsigned'=>true,'auto_increment'=>true],
            'nama_role' => ['type'=>'VARCHAR','constraint'=>50,'null'=>false],
            'judul_role' => ['type'=>'VARCHAR','constraint'=>50,'null'=>false],
            'keterangan' => ['type'=>'VARCHAR','constraint'=>50,'null'=>false],
            'id_module' => ['type'=>'SMALLINT','unsigned'=>true,'null'=>true],
        ]);
$this->forge->addKey('id_role', true);
        $this->forge->addUniqueKey('nama_role');
$this->forge->addForeignKey('id_module', 'module', 'id_module', 'CASCADE', 'CASCADE');
        $this->forge->createTable('role', true);
    }

    public function down()
    {
        $this->forge->dropTable('role', true);
    }
}
