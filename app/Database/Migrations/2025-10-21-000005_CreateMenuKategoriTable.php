<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenuKategoriTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
'id_menu_kategori' => ['type'=>'INT','unsigned'=>true,'auto_increment'=>true],
            'nama_kategori' => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'deskripsi' => ['type'=>'VARCHAR','constraint'=>255,'null'=>true],
            'aktif' => ['type'=>'ENUM','constraint'=>['Y','N'],'null'=>true],
            'show_title' => ['type'=>'ENUM','constraint'=>['Y','N'],'null'=>true],
            'urut' => ['type'=>'TINYINT','unsigned'=>true,'null'=>true],
        ]);
$this->forge->addKey('id_menu_kategori', true);

        $this->forge->createTable('menu_kategori', true);
    }

    public function down()
    {
        $this->forge->dropTable('menu_kategori', true);
    }
}
