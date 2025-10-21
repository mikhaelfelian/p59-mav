<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserTokenTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'selector' => ['type'=>'VARCHAR','constraint'=>255,'null'=>false],
            'token' => ['type'=>'VARCHAR','constraint'=>255,'null'=>false],
            'action' => ['type'=>'ENUM','constraint'=>['register','remember','recovery','activation'],'null'=>false],
            'id_user' => ['type'=>'INT','unsigned'=>true,'null'=>false],
            'created' => ['type'=>'DATETIME','null'=>false],
            'expires' => ['type'=>'DATETIME','null'=>false],
        ]);
$this->forge->addKey('selector', true);
$this->forge->addForeignKey('id_user', 'user', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_token', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_token', true);
    }
}
