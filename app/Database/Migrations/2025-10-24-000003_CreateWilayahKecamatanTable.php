<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWilayahKecamatanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_wilayah_kecamatan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_wilayah_kabupaten' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_kecamatan' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
        ]);
        $this->forge->addKey('id_wilayah_kecamatan', true);
        $this->forge->createTable('wilayah_kecamatan');
    }

    public function down()
    {
        $this->forge->dropTable('wilayah_kecamatan');
    }
}
