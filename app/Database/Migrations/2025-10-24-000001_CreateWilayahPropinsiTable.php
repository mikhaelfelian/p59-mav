<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWilayahPropinsiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_wilayah_propinsi' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_propinsi' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'ibukota' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'p_bsni' => ['type' => 'CHAR', 'constraint' => 5, 'null' => true],
        ]);
        $this->forge->addKey('id_wilayah_propinsi', true);
        $this->forge->createTable('wilayah_propinsi');
    }

    public function down()
    {
        $this->forge->dropTable('wilayah_propinsi');
    }
}
