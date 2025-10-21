<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id_user' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'email' => ['type'=>'VARCHAR','constraint'=>255],
            'username' => ['type'=>'VARCHAR','constraint'=>255],
            'nama' => ['type'=>'VARCHAR','constraint'=>255],
            'password' => ['type'=>'VARCHAR','constraint'=>255],
            'verified' => ['type'=>'TINYINT','constraint'=>4],
            'status' => ['type'=>'ENUM','constraint'=>['active','suspended','deleted'],'default'=>'active'],
            'created' => ['type'=>'DATETIME','default'=>'current_timestamp()'],
            'avatar' => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'default_page_type' => ['type'=>'ENUM','constraint'=>['url','id_module','id_role'],'null'=>true],
            'default_page_url' => ['type'=>'VARCHAR','constraint'=>50,'null'=>true],
            'default_page_id_module' => ['type'=>'SMALLINT','unsigned'=>true,'null'=>true],
            'default_page_id_role' => ['type'=>'SMALLINT','unsigned'=>true,'null'=>true],
        ]);
$this->forge->addKey('id_user', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addUniqueKey('username');
$this->forge->addForeignKey('default_page_id_module', 'module', 'id_module', 'SET NULL', 'SET NULL');
        $this->forge->addForeignKey('default_page_id_role', 'role', 'id_role', 'SET NULL', 'SET NULL');
        $this->forge->createTable('user', true);
    }

    public function down()
    {
        $this->forge->dropTable('user', true);
    }
}
