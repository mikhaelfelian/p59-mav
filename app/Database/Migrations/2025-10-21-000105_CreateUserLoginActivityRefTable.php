<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserLoginActivityRefTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id_activity' => ['type'=>'TINYINT','unsigned'=>true,'auto_increment'=>true],
            'value' => ['type'=>'VARCHAR','constraint'=>50,'null'=>false],
        ]);
$this->forge->addKey('id_activity', true);

        $this->forge->createTable('user_login_activity_ref', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_login_activity_ref', true);
    }
}
