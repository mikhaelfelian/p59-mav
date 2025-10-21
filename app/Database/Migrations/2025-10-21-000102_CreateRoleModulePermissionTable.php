<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRoleModulePermissionTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id_role' => ['type'=>'SMALLINT','unsigned'=>true,'null'=>false],
            'id_module_permission' => ['type'=>'SMALLINT','unsigned'=>true,'null'=>false],
        ]);
$this->forge->addKey(['id_role','id_module_permission'], true);
$this->forge->addForeignKey('id_role', 'role', 'id_role', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_module_permission', 'module_permission', 'id_module_permission', 'CASCADE', 'CASCADE');
        $this->forge->createTable('role_module_permission', true);
    }

    public function down()
    {
        $this->forge->dropTable('role_module_permission', true);
    }
}
