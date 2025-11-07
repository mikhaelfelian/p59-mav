<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInvoiceNoToSales extends Migration
{
	public function up()
	{
		// Add invoice_no column if it doesn't exist
		if (! $this->db->fieldExists('invoice_no', 'sales')) {
			$this->forge->addColumn('sales', [
				'invoice_no' => [
					'type'       => 'VARCHAR',
					'constraint' => 50,
					'null'       => true,
				],
			]);

			// Backfill: prefer invoice_code if it exists; otherwise generate a simple fallback
			if ($this->db->fieldExists('invoice_code', 'sales')) {
				$this->db->query('UPDATE `sales` SET `invoice_no` = `invoice_code` WHERE `invoice_no` IS NULL OR `invoice_no` = ""');
			}

			// Fallback generation for any remaining nulls
			$this->db->query(
				"UPDATE `sales` SET `invoice_no` = CONCAT('INV', DATE_FORMAT(COALESCE(`created_at`, NOW()), '%Y%m%d'), LPAD(`id`, 4, '0')) WHERE `invoice_no` IS NULL OR `invoice_no` = ''"
			);

			// Enforce NOT NULL and UNIQUE
			$this->db->query("ALTER TABLE `sales` MODIFY `invoice_no` VARCHAR(50) NOT NULL");
			// Add unique key if not present
			// Try creating; ignore if it already exists
			try {
				$this->db->query("ALTER TABLE `sales` ADD UNIQUE KEY `unique_invoice_no` (`invoice_no`)");
			} catch (\Throwable $e) {
				// Key might already exist; ignore
			}
		}
	}

	public function down()
	{
		// Drop unique key first if exists, then column
		try {
			$this->db->query("ALTER TABLE `sales` DROP INDEX `unique_invoice_no`");
		} catch (\Throwable $e) {
			// ignore if not exists
		}

		if ($this->db->fieldExists('invoice_no', 'sales')) {
			$this->forge->dropColumn('sales', 'invoice_no');
		}
	}
}


