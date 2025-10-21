<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserRoleTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id_user' => ['type'=>'INT','unsigned'=>true,'null'=>false],
            'id_role' => ['type'=>'SMALLINT','unsigned'=>true,'null'=>false],
        ]);
$this->forge->addKey(['id_user','id_role'], true);
$this->forge->addForeignKey('id_user', 'user', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_role', 'role', 'id_role', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_role', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_role', true);
    }
}
