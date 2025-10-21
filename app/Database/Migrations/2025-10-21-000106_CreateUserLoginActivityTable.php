<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserLoginActivityTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'id_user' => ['type'=>'INT','unsigned'=>true,'null'=>false],
            'id_activity' => ['type'=>'TINYINT','null'=>false],
            'time' => ['type'=>'DATETIME','null'=>false],
        ]);
$this->forge->addKey('id', true);
$this->forge->addForeignKey('id_user', 'user', 'id_user', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_login_activity', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_login_activity', true);
    }
}
