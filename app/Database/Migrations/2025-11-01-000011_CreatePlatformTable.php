<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-01
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating platform table to store external or internal sales and service platforms 
 * used in the system (e.g., marketplace integrations, POS extensions, or distribution platforms).
 * This file represents the Migration for CreatePlatformTable.
 */
class CreatePlatformTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
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
            'code' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
                'default'    => null,
            ],
            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => 160,
                'null'       => true,
                'default'    => null,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['0', '1'],
                'null'       => false,
                'default'    => '1',
            ],
            'status_sys' => [
                'type'       => 'ENUM',
                'constraint' => ['0', '1'],
                'null'       => false,
                'default'    => '0',
            ],
        ]);

        // Primary key
        $this->forge->addKey('id', true);

        // Indexes
        $this->forge->addKey('user_id', false, false, 'idx_user');
        $this->forge->addKey('code', false, false, 'idx_code');

        // Create table with comment
        $this->forge->createTable('platform', false, [
            'ENGINE' => 'InnoDB',
            'COMMENT' => 'This table stores available platforms (marketplaces or system integrations) for sales, configuration, and synchronization.'
        ]);

        // Set DEFAULT CURRENT_TIMESTAMP for created_at if null
        $this->db->query("ALTER TABLE `platform` MODIFY `created_at` DATETIME NULL DEFAULT NULL");
        
        // Set DEFAULT NULL and ON UPDATE CURRENT_TIMESTAMP for updated_at
        $this->db->query("ALTER TABLE `platform` MODIFY `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('platform', true);
    }
}

