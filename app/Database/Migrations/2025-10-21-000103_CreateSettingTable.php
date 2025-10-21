<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'type' => ['type'=>'VARCHAR','constraint'=>50,'null'=>false],
            'param' => ['type'=>'VARCHAR','constraint'=>255,'null'=>false],
            'value' => ['type'=>'TEXT','null'=>true],
        ]);
$this->forge->addKey(['type','param'], true);

        $this->forge->createTable('setting', true);
    }

    public function down()
    {
        $this->forge->dropTable('setting', true);
    }
}
