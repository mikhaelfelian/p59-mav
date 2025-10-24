<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWilayahKabupatenTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_wilayah_kabupaten' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_wilayah_propinsi' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nama_kabupaten' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'ibukota' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'k_bsni' => ['type' => 'CHAR', 'constraint' => 3, 'null' => true],
        ]);
        $this->forge->addKey('id_wilayah_kabupaten', true);
        $this->forge->createTable('wilayah_kabupaten');
    }

    public function down()
    {
        $this->forge->dropTable('wilayah_kabupaten');
    }
}
