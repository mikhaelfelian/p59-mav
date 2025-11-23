<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Created by: Mikhael Felian Waskito - mikhaelfelian@gmail.com
 * Date: 2025-11-21
 * Github: github.com/mikhaelfelian
 * Description: Migration for creating item_sn_replaced table to track serial number replacement history
 * This table records each replacement event, allowing tracking of how many times a serial number has been replaced.
 * This file represents the Migration for CreateItemSnReplacedTable.
 */
class CreateItemSnReplacedTable extends Migration
{
    public function up()
    {
        // Check if table already exists
        if (in_array('item_sn_replaced', $this->db->listTables())) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
                'comment'    => 'FK to item.id',
            ],
            'sn_old' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'Old serial number that was replaced',
            ],
            'sn_replaced' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'New serial number that replaced the old one',
            ],
            'created_at' => [
                'type'    => 'TIMESTAMP',
                'null'    => false,
                'default' => null,
            ],
        ]);

        $this->forge->addKey('id', true); // Primary key
        $this->forge->addKey('item_id');
        $this->forge->addKey('sn_old');
        $this->forge->addKey('sn_replaced');

        // Create table
        $this->forge->createTable('item_sn_replaced', false, [
            'ENGINE'    => 'InnoDB',
            'CHARSET'   => 'utf8mb4',
            'COLLATE'   => 'utf8mb4_general_ci',
            'COMMENT'   => 'Tracks serial number replacement history. Records each replacement event to allow counting how many times a SN has been replaced.',
        ]);

        // Set DEFAULT CURRENT_TIMESTAMP for created_at
        $this->db->query("ALTER TABLE `item_sn_replaced` MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Add foreign key constraint to item table
        if ($this->db->tableExists('item')) {
            try {
                // Check if constraint already exists
                $constraintExists = $this->checkForeignKeyExists('item_sn_replaced', 'FK_item_sn_replaced_item');
                if (!$constraintExists) {
                    $this->db->query("
                        ALTER TABLE `item_sn_replaced`
                        ADD CONSTRAINT `FK_item_sn_replaced_item` 
                        FOREIGN KEY (`item_id`) 
                        REFERENCES `item` (`id`) 
                        ON UPDATE CASCADE 
                        ON DELETE RESTRICT
                    ");
                }
            } catch (\Exception $e) {
                log_message('info', 'CreateItemSnReplacedTable: Could not add FK_item_sn_replaced_item: ' . $e->getMessage());
            }
        }
    }

    public function down()
    {
        // Drop foreign key constraint first
        if ($this->db->tableExists('item_sn_replaced')) {
            try {
                $this->db->query("ALTER TABLE `item_sn_replaced` DROP FOREIGN KEY `FK_item_sn_replaced_item`");
            } catch (\Exception $e) {
                log_message('info', 'CreateItemSnReplacedTable down: Could not drop FK_item_sn_replaced_item: ' . $e->getMessage());
            }
        }

        $this->forge->dropTable('item_sn_replaced', true);
    }

    /**
     * Check if a foreign key constraint exists on a table
     * 
     * @param string $tableName
     * @param string $constraintName
     * @return bool
     */
    protected function checkForeignKeyExists(string $tableName, string $constraintName): bool
    {
        try {
            $dbName = $this->db->database;
            $query = $this->db->query("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND CONSTRAINT_NAME = ? 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [$dbName, $tableName, $constraintName]);
            
            $result = $query->getRow();
            return !empty($result);
        } catch (\Exception $e) {
            // If we can't check, assume it doesn't exist to avoid errors
            return false;
        }
    }
}

