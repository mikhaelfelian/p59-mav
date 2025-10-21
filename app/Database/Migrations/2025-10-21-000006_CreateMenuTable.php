<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenuTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id_menu' => ['type'=>'SMALLINT','unsigned'=>true,'auto_increment'=>true],
            'nama_menu' => ['type'=>'VARCHAR','constraint'=>50],
            'id_menu_kategori' => ['type'=>'INT','unsigned'=>true,'null'=>true],
            'class' => ['type'=>'VARCHAR','constraint'=>50,'null'=>true],
            'url' => ['type'=>'VARCHAR','constraint'=>50,'null'=>true],
            'id_module' => ['type'=>'SMALLINT','unsigned'=>true,'null'=>true],
            'id_parent' => ['type'=>'SMALLINT','unsigned'=>true,'null'=>true],
            'aktif' => ['type'=>'TINYINT','default'=>1],
            'new' => ['type'=>'TINYINT','default'=>0],
            'urut' => ['type'=>'TINYINT','default'=>0],
        ]);
$this->forge->addKey('id_menu', true);
$this->forge->addForeignKey('id_module', 'module', 'id_module', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('id_parent', 'menu', 'id_menu', 'SET NULL', 'CASCADE');
        $this->forge->createTable('menu', true);
    }

    public function down()
    {
        $this->forge->dropTable('menu', true);
    }
}
