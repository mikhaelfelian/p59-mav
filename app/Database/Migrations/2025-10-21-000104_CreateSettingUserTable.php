<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingUserTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id_user' => ['type'=>'INT','unsigned'=>true,'null'=>false],
            'type' => ['type'=>'VARCHAR','constraint'=>50,'default'=>''],
            'param' => ['type'=>'VARCHAR','constraint'=>255,'null'=>false],
        ]);
$this->forge->addKey(['id_user','type'], true);

        $this->forge->createTable('setting_user', true);
    }

    public function down()
    {
        $this->forge->dropTable('setting_user', true);
    }
}
