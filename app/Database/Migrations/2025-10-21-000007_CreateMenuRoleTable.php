<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenuRoleTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id_menu' => ['type'=>'SMALLINT','unsigned'=>true],
            'id_role' => ['type'=>'SMALLINT','unsigned'=>true],
        ]);

$this->forge->addForeignKey('id_menu', 'menu', 'id_menu', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_role', 'role', 'id_role', 'CASCADE', 'CASCADE');
        $this->forge->createTable('menu_role', true);
    }

    public function down()
    {
        $this->forge->dropTable('menu_role', true);
    }
}
