<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-12
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating fee_type master table to store fee type definitions
 */
class CreateFeeTypeTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('fee_type')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'comment'    => 'Unique code, e.g., SHIPMENT, INSURANCE, HANDLING'
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => false,
                'comment'    => 'Display name, e.g., Ongkos Kirim, Asuransi'
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
                'comment'    => 'Optional description'
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['0', '1'],
                'null'       => false,
                'default'    => '1',
                'comment'    => '0=inactive, 1=active'
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
            'updated_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
            ],
        ]);

        // Primary key
        $this->forge->addKey('id', true);

        // Unique key for code
        $this->forge->addKey('code', false, true);

        // Index for status (to filter active ones)
        $this->forge->addKey('status', false, false, 'idx_status');

        // Create table
        $this->forge->createTable('fee_type', false, [
            'ENGINE' => 'InnoDB',
            'COMMENT' => 'Master table for fee types (shipment, insurance, handling, etc.)'
        ]);

        // Set DEFAULT CURRENT_TIMESTAMP for created_at
        $this->db->query("ALTER TABLE `fee_type` MODIFY `created_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP");

        // Set DEFAULT NULL and ON UPDATE CURRENT_TIMESTAMP for updated_at
        $this->db->query("ALTER TABLE `fee_type` MODIFY `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");

        // Insert initial fee types
        $initialFeeTypes = [
            [
                'code' => 'SHIPMENT',
                'name' => 'Ongkos Kirim',
                'description' => 'Biaya pengiriman barang',
                'status' => '1',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'INSURANCE',
                'name' => 'Asuransi',
                'description' => 'Biaya asuransi pengiriman',
                'status' => '1',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'HANDLING',
                'name' => 'Biaya Handling',
                'description' => 'Biaya penanganan barang',
                'status' => '1',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'PACKAGING',
                'name' => 'Biaya Kemasan',
                'description' => 'Biaya pengemasan barang',
                'status' => '1',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'code' => 'OTHER',
                'name' => 'Biaya Lainnya',
                'description' => 'Biaya tambahan lainnya',
                'status' => '1',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('fee_type')->insertBatch($initialFeeTypes);
    }

    public function down()
    {
        $this->forge->dropTable('fee_type', true);
    }
}
