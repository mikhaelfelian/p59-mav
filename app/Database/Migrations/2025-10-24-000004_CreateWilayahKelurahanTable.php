<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWilayahKelurahanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_wilayah_kelurahan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_wilayah_kecamatan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_kelurahan' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'kode_pos' => ['type' => 'CHAR', 'constraint' => 5, 'null' => true],
        ]);
        $this->forge->addKey('id_wilayah_kelurahan', true);
        $this->forge->createTable('wilayah_kelurahan');
    }

    public function down()
    {
        $this->forge->dropTable('wilayah_kelurahan');
    }
}
