<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWarehouseTable extends Migration
{
	public function up()
	{
		if ($this->db->tableExists('warehouse')) {
			return;
		}

		$this->forge->addField([
			'id' => [
				'type'           => 'BIGINT',
				'constraint'     => 20,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'name' => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'null'       => false,
			],
			'code' => [
				'type'       => 'VARCHAR',
				'constraint' => 50,
				'null'       => true,
			],
			'address' => [
				'type' => 'TEXT',
				'null' => true,
			],
			'is_active' => [
				'type'       => 'TINYINT',
				'constraint' => 1,
				'null'       => false,
				'default'    => 1,
			],
			'created_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
			'updated_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
		]);

		$this->forge->addKey('id', true);
		$this->forge->addKey('name');
		$this->forge->addKey('code');
		$this->forge->addKey('is_active');

		$this->forge->createTable('warehouse', false, [
			'ENGINE' => 'InnoDB',
			'COMMENT' => 'Warehouses for inventory and sales linkage'
		]);

		$this->db->query("ALTER TABLE `warehouse` MODIFY `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP");
		$this->db->query("ALTER TABLE `warehouse` MODIFY `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");
	}

	public function down()
	{
		if ($this->db->tableExists('warehouse')) {
			$this->forge->dropTable('warehouse', true);
		}
	}
}


